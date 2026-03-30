<?php
class BotController {
    private $db;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // --- TRẠM GÁC PHÂN QUYỀN ---
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Không phải admin -> Đá về trang chủ và báo lỗi (hoặc trang 403)
            header("Location: index.php?error=access_denied");
            exit();
        }
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
    /**
     * Hàm kích hoạt Bot Python từ Web
     * URL: index.php?role=admin&controller=bot&action=run&type=shopee
     */
    public function run() {
        set_time_limit(0); // Cho phép PHP chạy không giới hạn thời gian
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $output_array = [];
        $return_var = 0; // Biến lưu mã thoát của Python (0 = Thành công, 1 = Bị lỗi/Captcha)

        // Thực thi Bot tương ứng
        switch ($type) {
            case 'shopee':
                exec("python shopee_crawler.py 2>&1", $output_array, $return_var);
                break;
            case 'tiki':
                exec("python tiki_scraper.py 2>&1", $output_array, $return_var);
                break;
            case 'lazada': // Thêm case cho Lazada
                exec("python lazada_crawler.py 2>&1", $output_array, $return_var);
                break;
            case 'matcher':
                exec("python multi_platform_matcher.py 2>&1", $output_array, $return_var);
                break;
            default:
                $output_array[] = "Loại Bot không hợp lệ.";
                $return_var = -1;
        }

        // Chuyển mảng kết quả thành chuỗi
        $output_string = implode("\n", $output_array);

        session_start();
        
        // Kiểm tra mã thoát (Return Variable)
        if ($return_var === 1) {
            // Nếu Python trả về 1 (Chạy lệnh sys.exit(1) do đụng Captcha)
            $_SESSION['bot_status'] = 'error';
            $_SESSION['bot_message'] = "
                <div class='text-danger fw-bold mb-2'>
                    <i class='fas fa-exclamation-triangle'></i> BÁO ĐỘNG ĐỎ: BOT BỊ CHẶN (CAPTCHA/LOGIN)!
                </div>
                <div class='text-warning mb-3'>
                    Hệ thống đã tự động kích hoạt chế độ tự vệ (Dừng khẩn cấp) để bảo toàn dữ liệu.<br>
                    <strong>Yêu cầu:</strong> Vui lòng mở Command Prompt (CMD) tại thư mục chứa mã nguồn và chạy lệnh thủ công để giải quyết Captcha.
                </div>
                <hr style='border-color: #555;'>
                <div class='text-secondary small'>Chi tiết Log:</div>
                <pre class='text-danger' style='margin-top: 10px;'>$output_string</pre>";
        } elseif ($return_var === 0) {
            // Nếu Python trả về 0 (Thành công trọn vẹn)
            $_SESSION['bot_status'] = 'success';
            $_SESSION['bot_message'] = "
                <div class='text-success fw-bold mb-2'>
                    <i class='fas fa-check-circle'></i> BOT THỰC THI THÀNH CÔNG!
                </div>
                <hr style='border-color: #555;'>
                <pre>$output_string</pre>";
        } else {
            // Lỗi hệ thống khác
            $_SESSION['bot_status'] = 'warning';
            $_SESSION['bot_message'] = "<pre>$output_string</pre>";
        }
        
        header("Location: index.php?role=admin&controller=bot&action=index");
        exit();
    }
}