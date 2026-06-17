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

        $priceInsightStats = $this->getPriceInsightStats();
        $trendStats = $this->getPriceTrendStats();

        require_once 'views/admin/dashboard.php';
    }

    private function getScalar($sql) {
        $res = $this->db->query($sql);
        if (!$res) {
            return 0;
        }

        $row = $res->fetch_assoc();
        return (int) ($row['count'] ?? 0);
    }

    private function getPriceInsightStats() {
        return [
            'active_price_links' => $this->getScalar("
                SELECT COUNT(*) as count
                FROM platform_links
                WHERE is_active = 1
                  AND status = 1
                  AND availability_status = 'active'
                  AND current_price > 0
            "),
            'problem_links' => $this->getScalar("
                SELECT COUNT(*) as count
                FROM platform_links
                WHERE availability_status IN ('fetch_error', 'blocked_or_captcha', 'invalid_url', 'unknown', 'out_of_stock', 'temporarily_unavailable', 'discontinued')
                   OR is_active = 0
                   OR status = 0
            "),
            'history_points' => $this->getScalar("
                SELECT COUNT(*) as count
                FROM price_history
                WHERE price > 0
            "),
            'products_with_history' => $this->getScalar("
                SELECT COUNT(DISTINCT pl.product_id) as count
                FROM price_history ph
                JOIN platform_links pl ON ph.link_id = pl.id
                WHERE ph.price > 0
            "),
        ];
    }

    private function getPriceTrendStats() {
        $stats = [
            'increasing' => 0,
            'decreasing' => 0,
            'stable' => 0,
            'insufficient' => $this->getScalar("SELECT COUNT(*) as count FROM products"),
        ];

        $sql = "
            SELECT pl.product_id, DATE(ph.scraped_at) as price_date, MIN(ph.price) as min_price
            FROM price_history ph
            JOIN platform_links pl ON ph.link_id = pl.id
            WHERE ph.price > 0
            GROUP BY pl.product_id, DATE(ph.scraped_at)
            ORDER BY pl.product_id ASC, price_date ASC
        ";
        $res = $this->db->query($sql);
        if (!$res) {
            return $stats;
        }

        $pricesByProduct = [];
        while ($row = $res->fetch_assoc()) {
            $productId = (int) ($row['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $pricesByProduct[$productId][] = (int) ($row['min_price'] ?? 0);
        }

        foreach ($pricesByProduct as $prices) {
            $prices = array_values(array_filter($prices, static function ($price) {
                return $price > 0;
            }));

            if (count($prices) < 2) {
                continue;
            }

            $stats['insufficient'] = max(0, $stats['insufficient'] - 1);
            $first = (int) reset($prices);
            $last = (int) end($prices);
            $changePercent = $first > 0 ? (($last - $first) / $first) * 100 : 0;

            if ($changePercent >= 3) {
                $stats['increasing']++;
            } elseif ($changePercent <= -3) {
                $stats['decreasing']++;
            } else {
                $stats['stable']++;
            }
        }

        return $stats;
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
