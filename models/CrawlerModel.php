<?php
class CrawlerModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lấy thống kê tổng quan về hoạt động của Bot
     */
    public function getBotStatistics() {
        $stats = [
            'total_links' => 0,
            'error_links' => 0,
            'platforms' => []
        ];

        // 1. Tổng số link đang theo dõi trên tất cả các sàn
        $res = $this->conn->query("SELECT COUNT(*) as total FROM platform_links");
        if ($res) $stats['total_links'] = $res->fetch_assoc()['total'];

        // 2. Số link bị lỗi (Status = 3 mà hệ thống Python đã set khi kẹt Captcha/ẩn giá)
        $res = $this->conn->query("SELECT COUNT(*) as errors FROM platform_links WHERE status = 3");
        if ($res) $stats['error_links'] = $res->fetch_assoc()['errors'];

        // 3. Thống kê số lượng link theo từng sàn (Tiki, Shopee, Lazada)
        $res = $this->conn->query("SELECT platform_name, COUNT(*) as count FROM platform_links GROUP BY platform_name");
        if ($res) $stats['platforms'] = $res->fetch_all(MYSQLI_ASSOC);

        return $stats;
    }
}
?>