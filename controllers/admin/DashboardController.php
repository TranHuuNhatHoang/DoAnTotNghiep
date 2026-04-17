<?php
class DashboardController {
    private $db;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // --- TRẠM GÁC PHÂN QUYỀN ---
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php?error=access_denied");
            exit();
        }
        $this->db = $db;
    }

    // HIỂN THỊ TRANG TỔNG QUAN (Chỉ lấy số liệu thống kê)
    public function index() {
        $stats = [
            'total_products' => 0,
            'total_categories' => 0,
            'total_alerts' => 0,
            'total_users' => 0
        ];

        $res = $this->db->query("SELECT COUNT(*) as count FROM products");
        if ($res) $stats['total_products'] = $res->fetch_assoc()['count'];

        $res = $this->db->query("SELECT COUNT(*) as count FROM categories");
        if ($res) $stats['total_categories'] = $res->fetch_assoc()['count'];

        $res = $this->db->query("SELECT COUNT(*) as count FROM price_alerts");
        if ($res) $stats['total_alerts'] = $res->fetch_assoc()['count'];

        $res = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1");
        if ($res) $stats['total_users'] = $res->fetch_assoc()['count'];

        require_once 'views/admin/dashboard.php';
    }

    // HIỂN THỊ TRANG QUẢN LÝ CẢNH BÁO GIÁ
    public function alerts() {
        require_once 'models/ProductModel.php';
        $productModel = new ProductModel($this->db);
        $alerts = $productModel->getAllAlertsForAdmin();
        
        require_once 'views/admin/alerts.php';
    }
}
?>