<?php
class AdminProductController {
    private $db;
    private $productModel;
    private $categoryModel;

    public function __construct($db = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra quyền Admin
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
        require_once 'models/CategoryModel.php';
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    // Hiển thị danh sách sản phẩm
    public function index() {
        $products = $this->productModel->getAllProductsWithStats();
        $categories = $this->categoryModel->getAllCategories(); // Lấy danh mục để show ra Dropdown
        require_once 'views/admin/products.php';
    }

    // Xử lý thêm sản phẩm
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $categoryId = intval($_POST['category_id']);
            
            if (!empty($name)) {
                $this->productModel->createProduct($name, $description, $categoryId);
            }
        }
        header("Location: index.php?role=admin&controller=adminProduct&action=index");
    }

    // Xử lý sửa sản phẩm
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $categoryId = intval($_POST['category_id']);
            
            $this->productModel->updateProduct($id, $name, $description, $categoryId);
        }
        header("Location: index.php?role=admin&controller=adminProduct&action=index");
    }

    // Xử lý xóa sản phẩm
    public function delete() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id > 0) {
            $this->productModel->deleteProduct($id);
        }
        header("Location: index.php?role=admin&controller=adminProduct&action=index");
    }
}
?>
