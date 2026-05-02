<?php
require_once 'models/ProductModel.php';

class ProductController {
    private $db;
    private $productModel;

    public function __construct($db) {
        $this->db = $db;
        $this->productModel = new ProductModel($this->db);
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
    }

    public function index() {
        // Lấy danh mục cho Mega Menu
        require_once 'models/CategoryModel.php';
        $categoryModel = new CategoryModel($this->db);
        $categories = $categoryModel->getAllCategories();

        // Lấy dữ liệu cho các Section trang chủ
        $trending_products = $this->productModel->getTrendingProducts();
        $new_products = $this->productModel->getNewProducts();
        $top_deals = $this->productModel->getTopDeals();
        
        // --- BỔ SUNG LOGIC LẤY THÔNG BÁO CHO QUẢ CHUÔNG ---
        $notifications = [];
        $unread_count = 0;
        
        if (isset($_SESSION['user_id'])) {
            require_once 'models/UserModel.php';
            $userModel = new UserModel($this->db);
            $notifications = $userModel->getNotifications($_SESSION['user_id']);
            
            if ($notifications) {
                foreach($notifications as $n) { 
                    if($n['is_read'] == 0) {
                        $unread_count++; 
                    }
                }
            }
        }
        // --------------------------------------------------

        require_once 'views/user/home.php';
    }

    // Cập nhật hàm search cũ để nhận thêm Filter
    public function search() {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $catId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $platform = isset($_GET['platform_filter']) ? $_GET['platform_filter'] : null;
        if (!in_array($platform, ['Tiki', 'Shopee', 'Lazada'], true)) {
            $platform = null;
        }
        $minPrice = isset($_GET['min_price']) ? intval($_GET['min_price']) : null;
        $maxPrice = isset($_GET['max_price']) ? intval($_GET['max_price']) : null;
        $sort = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';

        $products = $this->productModel->searchProductsAdvanced($keyword, $catId, $platform, $minPrice, $maxPrice, $sort);
        
        // Lấy lại danh mục cho Mega Menu
        require_once 'models/CategoryModel.php';
        $categoryModel = new CategoryModel($this->db);
        $categories = $categoryModel->getAllCategories();

        require_once 'views/user/search_results.php'; // Bạn nên tạo thêm file này
    }

    // TRANG CHI TIẾT SẢN PHẨM
    public function detail($id) {
        if (!$id) { header("Location: index.php"); exit(); }

        $product = $this->productModel->getById($id);
        if (!$product) die("Sản phẩm không tồn tại.");

        $platforms = $this->productModel->getPlatformsByProductId($id);
        $priceHistory = $this->productModel->getPriceHistory($id);
        $productSpecs = $this->productModel->getProductSpecifications($id);
        
        // NÂNG CẤP: Lấy thống kê giá và Sản phẩm liên quan
        $priceStats = $this->productModel->getPriceStats($id);
        $relatedProducts = $this->productModel->getRelatedProducts($product['category_id'] ?? 0, $id);

        // NÂNG CẤP: Xử lý thông báo (Notification) chuẩn MVC (Không query trong View nữa)
        $myNotifs = [];
        $unreadCount = 0;
        $userAlert = null;
        
        if (isset($_SESSION['user_id'])) {
            $userAlert = $this->productModel->getPriceAlert($_SESSION['user_id'], $id);
            
            require_once 'models/UserModel.php';
            $userModel = new UserModel($this->db);
            $myNotifs = $userModel->getNotifications($_SESSION['user_id']);
            if ($myNotifs) {
                foreach($myNotifs as $n) { if($n['is_read'] == 0) $unreadCount++; }
            }
        }

        // Sắp xếp platforms theo giá từ thấp đến cao (Phục vụ bảng so sánh)
        foreach ($platforms as &$platform) {
            $hasValidPrice = (int) ($platform['has_valid_price'] ?? 0) === 1;
            $platform['has_valid_price'] = $hasValidPrice ? 1 : 0;
            if (!$hasValidPrice) {
                $platform['current_price'] = 0;
            }
        }
        unset($platform);

        usort($platforms, function($a, $b) {
            $validCompare = ((int) ($b['has_valid_price'] ?? 0)) <=> ((int) ($a['has_valid_price'] ?? 0));
            if ($validCompare !== 0) {
                return $validCompare;
            }

            $priceCompare = ((int) ($a['current_price'] ?? 0)) <=> ((int) ($b['current_price'] ?? 0));
            if ($priceCompare !== 0) {
                return $priceCompare;
            }

            return strcmp((string) ($a['platform_name'] ?? ''), (string) ($b['platform_name'] ?? ''));
        });

        require_once 'views/user/detail.php';
    }

    // XỬ LÝ LƯU MỨC GIÁ MONG MUỐN TỪ FORM
    public function setAlert() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $product_id = intval($_POST['product_id']);
            // Loại bỏ dấu phẩy/chấm nếu người dùng nhập kiểu 4.500.000
            $target_price = intval(str_replace(['.', ','], '', trim($_POST['target_price']))); 

            if ($product_id > 0 && $target_price > 0) {
                $this->productModel->setPriceAlert($_SESSION['user_id'], $product_id, $target_price);
                header("Location: index.php?role=user&controller=product&action=detail&id=$product_id&msg=alert_success");
                exit();
            }
        }
        header("Location: index.php");
        exit();
    }
    // XỬ LÝ HỦY THEO DÕI GIÁ
    public function removeAlert() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        if (isset($_GET['id'])) {
            $product_id = intval($_GET['id']);
            if ($product_id > 0) {
                // Xóa khỏi Database
                $this->productModel->deletePriceAlert($_SESSION['user_id'], $product_id);
                if (($_GET['redirect'] ?? '') === 'my_alerts') {
                    header("Location: index.php?role=user&controller=product&action=myAlerts&msg=alert_removed");
                    exit();
                }
                // Quay lại trang chi tiết kèm thông báo
                header("Location: index.php?role=user&controller=product&action=detail&id=$product_id&msg=alert_removed");
                exit();
            }
        }
        header("Location: index.php");
        exit();
    }
    // Xử lý khi User click vào một thông báo trên quả chuông
    /**
     * Xử lý khi click vào thông báo trên quả chuông
     */
    public function readNotification() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        // 2. Lấy và làm sạch tham số từ URL
        $notif_id = isset($_GET['notif_id']) ? intval($_GET['notif_id']) : 0;
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

        // 3. Nếu có ID thông báo, thực hiện đánh dấu đã đọc trong Database
        if ($notif_id > 0) {
            require_once 'models/UserModel.php';
            $userModel = new UserModel($this->db);
            $userModel->markNotificationRead($notif_id, $_SESSION['user_id']);
        }

        // 4. Luôn chuyển hướng người dùng đến trang chi tiết sản phẩm để xem giá mới
        header("Location: index.php?role=user&controller=product&action=detail&id=" . $product_id);
        exit();
    }

    /**
     * Hiển thị trang "Danh sách theo dõi" cá nhân
     */
    public function myAlerts() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        require_once 'models/UserModel.php';
        $userModel = new UserModel($this->db);
        $alerts = $userModel->getUserAlerts($_SESSION['user_id']);

        require_once 'views/user/my_alerts.php';
    }
    // API trả về gợi ý tìm kiếm (JSON)
    public function suggest() {
        header('Content-Type: application/json; charset=utf-8');
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        if (mb_strlen($keyword) < 2) { echo json_encode([]); exit(); }

        $suggestions = $this->productModel->getSuggestions($keyword);
        echo json_encode($suggestions);
        exit();
    }

    
}

?>
