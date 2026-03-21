<?php
require_once 'models/ProductModel.php';

class ProductController {
    private $db;
    private $productModel;

    public function __construct($db) {
        $this->db = $db;
        $this->productModel = new ProductModel($this->db);
    }

    /**
     * Trang chủ phía người dùng
     */
    public function index() {
        $products = []; 
        require_once 'views/user/home.php';
    }

    /**
     * Xử lý tìm kiếm sản phẩm
     */
    public function search() {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $products = [];
        if (!empty($keyword)) {
            $products = $this->productModel->searchProducts($keyword);
        }
        require_once 'views/user/home.php';
    }

    /**
     * Trang chi tiết sản phẩm và Biểu đồ biến động giá
     * URL: index.php?role=user&controller=product&action=detail&id=...
     */
    public function detail($id) {
        if (!$id) {
            header("Location: index.php");
            exit();
        }

        // 1. Lấy thông tin cơ bản của sản phẩm
        $product = $this->productModel->getById($id);
        if (!$product) {
            die("Sản phẩm không tồn tại.");
        }

        // 2. Lấy danh sách các sàn đang bán sản phẩm này
        $platforms = $this->productModel->getPlatformsByProductId($id);

        // 3. Lấy lịch sử giá (Dữ liệu thô từ Database)
        $priceHistory = $this->productModel->getPriceHistory($id);

        // 4. Gọi View hiển thị
        require_once 'views/user/detail.php';
    }
}
?>