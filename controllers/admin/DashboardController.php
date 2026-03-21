<?php
require_once 'models/ProductModel.php';

class DashboardController {
    private $db;
    private $productModel;

    public function __construct($db) {
        $this->db = $db;
        $this->productModel = new ProductModel($this->db);
    }

    // Hiển thị danh sách sản phẩm (Dashboard chính)
    public function index() {
        $products = $this->productModel->getAllProductsWithStats();
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
                // Bước A: Tạo sản phẩm gốc
                $productId = $this->productModel->createProduct($name, $description);

                if ($productId) {
                    // Bước B: Lưu Link Tiki vào bảng platform_links
                    $this->productModel->addPlatformLink($productId, 'Tiki', $tiki_url);
                    
                    // Thông báo thành công (Bạn có thể dùng Session để hiện Alert)
                    header("Location: index.php?role=admin&controller=dashboard&action=index&msg=success");
                    exit();
                }
            }
        }
        // Nếu lỗi, quay lại trang thêm
        header("Location: index.php?role=admin&controller=dashboard&action=add&msg=error");
        exit();
    }
}