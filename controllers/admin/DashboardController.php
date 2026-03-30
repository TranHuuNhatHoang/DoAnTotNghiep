<?php
require_once 'models/ProductModel.php';
require_once 'models/CrawlerModel.php'; // Đã thêm model mới

class DashboardController {
    private $db;
    private $productModel;
    private $crawlerModel;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // --- TRẠM GÁC PHÂN QUYỀN ---
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Không phải admin -> Đá về trang chủ và báo lỗi (hoặc trang 403)
            header("Location: index.php?error=access_denied");
            exit();
        }
        $this->db = $db;
        $this->productModel = new ProductModel($this->db);
        $this->crawlerModel = new CrawlerModel($this->db); // Khởi tạo
    }

    // Hiển thị danh sách sản phẩm (Dashboard chính)
    public function index() {
        $products = $this->productModel->getAllProductsWithStats();
        $botStats = $this->crawlerModel->getBotStatistics(); // Lấy số liệu Bot
        
        require_once 'views/admin/dashboard.php';
    }

    // 1. Hiển thị Form thêm sản phẩm
    public function add() {
        require_once 'views/admin/add_product.php';
    }

    // 2. Xử lý lưu dữ liệu từ Form vào Database
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $tiki_url = trim($_POST['tiki_url']);

            if (!empty($name) && !empty($tiki_url)) {
                $productId = $this->productModel->createProduct($name, $description);

                if ($productId) {
                    $this->productModel->addPlatformLink($productId, 'Tiki', $tiki_url);
                    header("Location: index.php?role=admin&controller=dashboard&action=index&msg=success");
                    exit();
                }
            }
        }
        header("Location: index.php?role=admin&controller=dashboard&action=add&msg=error");
        exit();
    }
    // Hiển thị trang Quản lý Cảnh báo Giá
    public function alerts() {
        // Nạp Model nếu trong __construct chưa có
        if (!isset($this->productModel)) {
            require_once 'models/ProductModel.php';
            $this->productModel = new ProductModel($this->db);
        }
        
        // Lấy toàn bộ danh sách cảnh báo
        $alerts = $this->productModel->getAllAlertsForAdmin();
        
        // Gọi View hiển thị
        require_once 'views/admin/alerts.php';
    }
}
?>