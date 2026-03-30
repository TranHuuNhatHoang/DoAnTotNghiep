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
        // Lấy danh sách sản phẩm nổi bật (Lấy tạm 6 sản phẩm mới nhất)
        $trending_products = $this->productModel->getAllProductsWithStats();
        $trending_products = array_slice($trending_products, 0, 6); // Cắt lấy 6 cái đầu tiên
        
        require_once 'views/user/home.php';
    }

    public function search() {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $products = [];
        if (!empty($keyword)) {
            $products = $this->productModel->searchProducts($keyword);
        }
        require_once 'views/user/home.php';
    }

    // TRANG CHI TIẾT SẢN PHẨM
    public function detail($id) {
        if (!$id) {
            header("Location: index.php");
            exit();
        }

        $product = $this->productModel->getById($id);
        if (!$product) die("Sản phẩm không tồn tại.");

        $platforms = $this->productModel->getPlatformsByProductId($id);
        $priceHistory = $this->productModel->getPriceHistory($id);

        // KIỂM TRA TRẠNG THÁI CẢNH BÁO GIÁ CỦA USER ĐANG ĐĂNG NHẬP
        $userAlert = null;
        if (isset($_SESSION['user_id'])) {
            $userAlert = $this->productModel->getPriceAlert($_SESSION['user_id'], $id);
        }

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
                // Quay lại trang chi tiết kèm thông báo
                header("Location: index.php?role=user&controller=product&action=detail&id=$product_id&msg=alert_removed");
                exit();
            }
        }
        header("Location: index.php");
        exit();
    }
}
?>