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
            $_SESSION['bot_message'] = "<pre>Chuc nang chay bot tu web dang bi tat. Bat BOT_ALLOW_WEB_RUN=true trong .env neu can dung.</pre>";
            header("Location: index.php?role=admin&controller=bot&action=index");
            exit();
        }

        set_time_limit(0);

        $type = $_GET['type'] ?? '';
        $scripts = [
            'shopee' => 'shopee_crawler.py',
            'tiki' => 'tiki_scraper.py',
            'lazada' => 'lazada_crawler.py',
            'matcher' => 'multi_platform_matcher.py',
        ];

        if (!isset($scripts[$type])) {
            $_SESSION['bot_status'] = 'warning';
            $_SESSION['bot_message'] = "<pre>Loai bot khong hop le.</pre>";
            header("Location: index.php?role=admin&controller=bot&action=index");
            exit();
        }

        $scriptPath = realpath(__DIR__ . '/../../' . $scripts[$type]);
        if (!$scriptPath || !is_readable($scriptPath)) {
            $_SESSION['bot_status'] = 'error';
            $_SESSION['bot_message'] = "<pre>Khong tim thay file bot.</pre>";
            header("Location: index.php?role=admin&controller=bot&action=index");
            exit();
        }

        $python = AppEnv::get('PYTHON_BIN', 'python');
        $command = escapeshellarg($python) . ' ' . escapeshellarg($scriptPath) . ' 2>&1';

        $outputArray = [];
        $returnVar = 0;
        exec($command, $outputArray, $returnVar);

        $outputString = htmlspecialchars(implode("\n", $outputArray), ENT_QUOTES, 'UTF-8');

        if ($returnVar === 1) {
            $_SESSION['bot_status'] = 'error';
            $_SESSION['bot_message'] = "
                <div class='text-danger fw-bold mb-2'>
                    <i class='fas fa-exclamation-triangle'></i> BOT BI CHAN CAPTCHA/LOGIN
                </div>
                <div class='text-warning mb-3'>
                    Bot da dung de tranh ghi sai du lieu. Hay chay script bang CMD/Terminal de xu ly captcha thu cong.
                </div>
                <hr style='border-color: #555;'>
                <div class='text-secondary small'>Chi tiet log:</div>
                <pre class='text-danger' style='margin-top: 10px;'>{$outputString}</pre>";
        } elseif ($returnVar === 0) {
            $_SESSION['bot_status'] = 'success';
            $_SESSION['bot_message'] = "
                <div class='text-success fw-bold mb-2'>
                    <i class='fas fa-check-circle'></i> BOT THUC THI THANH CONG
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
