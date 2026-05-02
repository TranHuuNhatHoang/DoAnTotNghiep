<?php
require_once 'config/env.php';

class BotController {
    private $db;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php?error=access_denied");
            exit();
        }

        $this->db = $db;
    }

    public function index() {
        $sql = "SELECT platform_name, MAX(last_scraped_at) as last_run
                FROM platform_links GROUP BY platform_name";
        $result = $this->db->query($sql);
        $botStats = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        require_once 'views/admin/bot_management.php';
    }

    public function run() {
        if (!AppEnv::bool('BOT_ALLOW_WEB_RUN', true)) {
            $_SESSION['bot_status'] = 'error';
            $_SESSION['bot_message'] = "<pre>Chức năng chạy bot từ web đang bị tắt. Bật BOT_ALLOW_WEB_RUN=true trong .env nếu cần dùng.</pre>";
            header("Location: index.php?role=admin&controller=bot&action=index");
            exit();
        }

        set_time_limit(0);

        $type = $_GET['type'] ?? '';
        $scripts = [
            'shopee' => 'crawlers/shopee_crawler.py',
            'tiki' => 'crawlers/tiki_scraper.py',
            'lazada' => 'crawlers/lazada_crawler.py',
            'matcher' => 'crawlers/multi_platform_matcher.py',
        ];
        $botNames = [
            'shopee' => 'Shopee Crawler',
            'tiki' => 'Tiki Scraper',
            'lazada' => 'Lazada Crawler',
            'matcher' => 'Fuzzy Matcher',
        ];

        if (!isset($scripts[$type])) {
            $_SESSION['bot_status'] = 'warning';
            $_SESSION['bot_message'] = "<pre>Loại bot không hợp lệ.</pre>";
            header("Location: index.php?role=admin&controller=bot&action=index");
            exit();
        }

        $scriptPath = realpath(__DIR__ . '/../../' . $scripts[$type]);
        if (!$scriptPath || !is_readable($scriptPath)) {
            $_SESSION['bot_status'] = 'error';
            $_SESSION['bot_message'] = "<pre>Không tìm thấy file bot.</pre>";
            header("Location: index.php?role=admin&controller=bot&action=index");
            exit();
        }

        $python = AppEnv::get('PYTHON_BIN', 'python');
        $command = escapeshellarg($python) . ' ' . escapeshellarg($scriptPath) . ' 2>&1';

        $outputArray = [];
        $returnVar = 0;
        exec($command, $outputArray, $returnVar);

        $outputString = htmlspecialchars(implode("\n", $outputArray), ENT_QUOTES, 'UTF-8');
        $botName = $botNames[$type] ?? 'Bot';

        if ($returnVar === 2) {
            $_SESSION['bot_status'] = 'warning';
            $_SESSION['bot_message'] = "
                <div class='text-warning fw-bold mb-2'>
                    <i class='fas fa-shield-halved'></i> {$botName} yêu cầu captcha/đăng nhập
                </div>
                <div class='text-warning mb-3'>
                    Bot đã dừng an toàn. Nếu có link bị chặn, hệ thống sẽ giữ dữ liệu cũ và tạm bỏ qua theo thời gian chờ.
                    Hãy chạy script bằng CMD/Terminal để xử lý xác minh thủ công trong cửa sổ Chrome.
                </div>
                <hr style='border-color: #555;'>
                <div class='text-secondary small'>Chi tiết log:</div>
                <pre class='text-warning' style='margin-top: 10px;'>{$outputString}</pre>";
        } elseif ($returnVar === 1) {
            $_SESSION['bot_status'] = 'error';
            $_SESSION['bot_message'] = "
                <div class='text-danger fw-bold mb-2'>
                    <i class='fas fa-exclamation-triangle'></i> {$botName} gặp lỗi khi thực thi
                </div>
                <div class='text-warning mb-3'>
                    Vui lòng xem log bên dưới. Nếu lỗi đến từ captcha/đăng nhập, hãy chạy script bằng CMD/Terminal để xử lý thủ công.
                </div>
                <hr style='border-color: #555;'>
                <div class='text-secondary small'>Chi tiết log:</div>
                <pre class='text-danger' style='margin-top: 10px;'>{$outputString}</pre>";
        } elseif ($returnVar === 0) {
            $_SESSION['bot_status'] = 'success';
            $_SESSION['bot_message'] = "
                <div class='text-success fw-bold mb-2'>
                    <i class='fas fa-check-circle'></i> {$botName} thực thi thành công
                </div>
                <hr style='border-color: #555;'>
                <pre>{$outputString}</pre>";
        } else {
            $_SESSION['bot_status'] = 'warning';
            $_SESSION['bot_message'] = "<pre>{$outputString}</pre>";
        }

        header("Location: index.php?role=admin&controller=bot&action=index");
        exit();
    }
}
?>
