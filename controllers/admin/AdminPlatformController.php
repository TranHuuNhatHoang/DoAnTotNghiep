<?php
class AdminPlatformController {
    private $db;
    private $productModel;

    public function __construct($db = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
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

    // Hiển thị danh sách link của 1 sản phẩm
    public function index() {
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        
        if ($product_id === 0) {
            header("Location: index.php?role=admin&controller=adminProduct&action=index");
            exit();
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

    // Xử lý Thêm Link (Hàm addPlatformLink của bạn dùng cơ chế Upsert rất hay)
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id']);
            $platform_name = trim($_POST['platform_name']);
            $url = trim($_POST['product_url']);
            
            if (!empty($url) && in_array($platform_name, ['Tiki', 'Shopee', 'Lazada'])) {
                $this->productModel->addPlatformLink($product_id, $platform_name, $url);
            }
            header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $product_id);
            exit();
        }
    }

    // Xử lý Sửa Link
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id']); // Để redirect về đúng trang
            $link_id = intval($_POST['link_id']);
            $url = trim($_POST['product_url']);
            $is_active = isset($_POST['is_active']) ? 1 : 0; // Checkbox
            
            $this->productModel->updatePlatformLink($link_id, $url, $is_active);
            header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $product_id);
            exit();
        }
    }

    // Xử lý Xóa Link
    public function delete() {
        $link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

        if ($link_id > 0) {
            $this->productModel->deletePlatformLink($link_id);
        }
        header("Location: index.php?role=admin&controller=adminPlatform&action=index&product_id=" . $product_id);
        exit();
    }
}
?>
