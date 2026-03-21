<?php
class BotController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Trang quản lý trạng thái các con Bot
     */
    public function index() {
        // Lấy thời gian cập nhật gần nhất của từng sàn để hiển thị trạng thái
        $sql = "SELECT platform_name, MAX(last_scraped_at) as last_run 
                FROM platform_links GROUP BY platform_name";
        $result = $this->db->query($sql);
        $botStats = $result->fetch_all(MYSQLI_ASSOC);

        require_once 'views/admin/bot_management.php';
    }

    /**
     * Hàm kích hoạt Bot Python từ Web
     * URL: index.php?role=admin&controller=bot&action=run&type=shopee
     */
    public function run() {
        set_time_limit(0);
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $output = "";

        // Chú ý: Đường dẫn tới python và file .py phải chính xác
        // Bạn có thể dùng đường dẫn tuyệt đối nếu shell_exec không tìm thấy file
        switch ($type) {
            case 'shopee':
                $output = shell_exec("python shopee_crawler.py 2>&1");
                break;
            case 'tiki':
                $output = shell_exec("python tiki_scraper.py 2>&1");
                break;
            case 'matcher':
                $output = shell_exec("python auto_matcher.py 2>&1");
                break;
            default:
                $output = "Loại Bot không hợp lệ.";
        }

        // Lưu thông báo vào Session để hiển thị kết quả sau khi Redirect
        session_start();
        $_SESSION['bot_message'] = "Kết quả thực thi Bot $type: <br><pre>$output</pre>";
        
        header("Location: index.php?role=admin&controller=bot&action=index");
        exit();
    }
}