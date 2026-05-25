<?php
class AdminPlatformController {
    private $db;
    private $productModel;

    public function __construct($db = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Phiên admin đã hết hạn hoặc bạn không có quyền thực hiện thao tác này.',
                    'login_url' => 'index.php',
                ], 401);
            }

            header("Location: index.php");
            exit();
        }

        $this->db = $db;
        if (!$this->db) {
            require_once 'config/database.php';
            $database = new Database();
            $this->db = $database->getConnection();
        }
        require_once 'models/ProductModel.php';
        $this->productModel = new ProductModel($this->db);
    }

    private function isAjaxRequest() {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return strtolower($requestedWith) === 'xmlhttprequest'
            || stripos($accept, 'application/json') !== false;
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Hiển thị danh sách link của 1 sản phẩm
    public function index() {
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        
        if ($product_id === 0) {
            $filters = $this->getOverviewFilters();
            $products = $this->getOverviewProducts($filters);
            $platformStats = $this->getPlatformStats();
            $productPlatformMap = $this->getProductPlatformMap();

            if ($this->isAjaxRequest()) {
                $platformOverviewPartialOnly = true;
                require_once 'views/admin/platforms_overview.php';

                $this->jsonResponse([
                    'success' => true,
                    'stats_html' => render_admin_platform_overview_stats($platformStats),
                    'table_html' => render_admin_platform_overview_table($products, $productPlatformMap),
                    'result_count' => count($products),
                ]);
            }

            require_once 'views/admin/platforms_overview.php';
            return;
        }

        // Lấy tên sản phẩm để hiển thị trên tiêu đề
        $product = $this->productModel->getById($product_id);
        // Lấy các link hiện có (Bao gồm cả link đang tắt is_active = 0)
        $stmt = $this->db->prepare("SELECT * FROM platform_links WHERE product_id = ? ORDER BY platform_name ASC");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $links = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        require_once 'views/admin/platforms.php';
    }

    private function getOverviewFilters() {
        $allowedPlatforms = ['Tiki', 'Shopee', 'Lazada'];
        $allowedStatuses = [
            'unknown',
            'active',
            'out_of_stock',
            'temporarily_unavailable',
            'discontinued',
            'invalid_url',
            'fetch_error',
            'blocked_or_captcha',
            'needs_check',
        ];

        $platform = $_GET['platform'] ?? '';
        $availabilityStatus = $_GET['availability_status'] ?? '';

        return [
            'platform' => in_array($platform, $allowedPlatforms, true) ? $platform : '',
            'availability_status' => in_array($availabilityStatus, $allowedStatuses, true) ? $availabilityStatus : '',
        ];
    }

    private function getOverviewProducts($filters) {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['platform'])) {
            $where[] = "pl.platform_name = ?";
            $params[] = $filters['platform'];
            $types .= 's';
        }

        if (!empty($filters['availability_status'])) {
            if ($filters['availability_status'] === 'needs_check') {
                $where[] = "(pl.availability_status IN ('fetch_error', 'blocked_or_captcha', 'invalid_url', 'unknown') OR pl.next_check_at <= NOW())";
            } else {
                $where[] = "pl.availability_status = ?";
                $params[] = $filters['availability_status'];
                $types .= 's';
            }
        }

        if (empty($where)) {
            return $this->productModel->getAllProductsWithStats();
        }

        $sql = "SELECT DISTINCT p.*,
                       c.name as category_name,
                       (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND is_active = 1) as total_active_links,
                       (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price,
                       (SELECT MAX(last_checked_at) FROM platform_links WHERE product_id = p.id) as last_update
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN platform_links pl ON pl.product_id = p.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getPlatformStats() {
        $sql = "SELECT platform_name,
                       COUNT(*) as total_links,
                       SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_links,
                       SUM(CASE WHEN availability_status IN ('fetch_error', 'blocked_or_captcha', 'invalid_url') THEN 1 ELSE 0 END) as problem_links,
                       MAX(last_scraped_at) as last_scraped_at
                FROM platform_links
                GROUP BY platform_name";
        $result = $this->db->query($sql);

        $stats = [
            'Tiki' => ['total_links' => 0, 'active_links' => 0, 'problem_links' => 0, 'last_scraped_at' => null],
            'Shopee' => ['total_links' => 0, 'active_links' => 0, 'problem_links' => 0, 'last_scraped_at' => null],
            'Lazada' => ['total_links' => 0, 'active_links' => 0, 'problem_links' => 0, 'last_scraped_at' => null],
        ];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stats[$row['platform_name']] = $row;
            }
        }

        return $stats;
    }

    private function getProductPlatformMap() {
        $sql = "SELECT product_id,
                       MAX(CASE WHEN platform_name = 'Tiki' THEN is_active ELSE NULL END) as tiki_active,
                       MAX(CASE WHEN platform_name = 'Shopee' THEN is_active ELSE NULL END) as shopee_active,
                       MAX(CASE WHEN platform_name = 'Lazada' THEN is_active ELSE NULL END) as lazada_active,
                       MAX(CASE WHEN platform_name = 'Tiki' THEN availability_status ELSE NULL END) as tiki_status,
                       MAX(CASE WHEN platform_name = 'Shopee' THEN availability_status ELSE NULL END) as shopee_status,
                       MAX(CASE WHEN platform_name = 'Lazada' THEN availability_status ELSE NULL END) as lazada_status
                FROM platform_links
                GROUP BY product_id";
        $result = $this->db->query($sql);
        $map = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $map[(int) $row['product_id']] = $row;
            }
        }

        return $map;
    }

    // Xử lý Thêm Link (Hàm addPlatformLink của bạn dùng cơ chế Upsert rất hay)
    public function add() {
        $isAjax = $this->isAjaxRequest();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id'] ?? 0);
            $platform_name = trim($_POST['platform_name'] ?? '');
            $url = trim($_POST['product_url'] ?? '');
            $error = '';
            
            if ($product_id <= 0 || empty($url) || !in_array($platform_name, ['Tiki', 'Shopee', 'Lazada'])) {
                $error = 'Link hoặc sàn không hợp lệ.';
            } elseif (!$this->productModel->addPlatformLink($product_id, $platform_name, $url)) {
                $error = 'Không thể lưu link. Link này có thể đã tồn tại ở sản phẩm khác hoặc dữ liệu link không hợp lệ.';
            }
            if ($error !== '') {
                if ($isAjax) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => $error,
                    ], 422);
                }

                $_SESSION['admin_error'] = $error;
            } elseif ($isAjax) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Đã gắn link sàn vào sản phẩm.',
                    'product_id' => $product_id,
                    'reload' => true,
                ]);
            }

            header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $product_id);
            exit();
        }

        if ($isAjax) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Yêu cầu thêm link không hợp lệ.',
            ], 405);
        }
    }

    // Xử lý Sửa Link
    public function update() {
        $isAjax = $this->isAjaxRequest();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id'] ?? 0); // Để redirect về đúng trang
            $link_id = intval($_POST['link_id'] ?? 0);
            $url = trim($_POST['product_url'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0; // Checkbox
            $error = '';
            
            if ($link_id <= 0 || empty($url)) {
                $error = 'Link không hợp lệ.';
            } elseif (!$this->productModel->updatePlatformLink($link_id, $url, $is_active)) {
                $error = 'Không thể cập nhật link. Link này có thể đã tồn tại ở sản phẩm khác hoặc dữ liệu link không hợp lệ.';
            }
            if ($error !== '') {
                if ($isAjax) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => $error,
                    ], 422);
                }

                $_SESSION['admin_error'] = $error;
            } elseif ($isAjax) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Đã cập nhật link sàn.',
                    'product_id' => $product_id,
                    'link_id' => $link_id,
                    'reload' => true,
                ]);
            }
            header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $product_id);
            exit();
        }

        if ($isAjax) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Yêu cầu cập nhật link không hợp lệ.',
            ], 405);
        }
    }

    // Xử lý Xóa Link
    public function delete() {
        $isAjax = $this->isAjaxRequest();
        $link_id = intval($_POST['id'] ?? $_POST['link_id'] ?? $_GET['id'] ?? 0);
        $product_id = intval($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

        if ($link_id > 0) {
            $deleted = $this->productModel->deletePlatformLink($link_id);
            if ($isAjax) {
                if (!$deleted) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Không thể xóa link sàn. Vui lòng thử lại.',
                    ], 500);
                }

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Đã xóa link sàn.',
                    'product_id' => $product_id,
                    'link_id' => $link_id,
                ]);
            }
        } elseif ($isAjax) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Link cần xóa không hợp lệ.',
            ], 422);
        }
        header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $product_id);
        exit();
    }
}
?>
