<?php
class AdminCategoryController {
    private $db;
    private $categoryModel; // SỬA ĐỔI: Dùng CategoryModel

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php");
            exit();
        }

        $this->db = new mysqli("127.0.0.1", "root", "", "web_test", 3307);
        
        // SỬA ĐỔI: Nạp file CategoryModel
        require_once 'models/CategoryModel.php';
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index() {
        $categories = $this->categoryModel->getAllCategories();
        require_once 'views/admin/categories.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $icon = trim($_POST['icon']);
            if (!empty($name)) {
                $this->categoryModel->addCategory($name, $icon);
            }
        }
        header("Location: index.php?role=admin&controller=adminCategory&action=index");
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $icon = trim($_POST['icon']);
            $this->categoryModel->updateCategory($id, $name, $icon);
        }
        header("Location: index.php?role=admin&controller=adminCategory&action=index");
    }

    public function delete() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id > 0) {
            $this->categoryModel->deleteCategory($id);
        }
        header("Location: index.php?role=admin&controller=adminCategory&action=index");
    }
}