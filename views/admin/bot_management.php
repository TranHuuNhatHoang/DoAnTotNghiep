<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$botStats = $botStats ?? [];
$botTaskWarnings = $botTaskWarnings ?? [];
$lastRunByPlatform = [];
foreach ($botStats as $stat) {
    $lastRunByPlatform[$stat['platform_name']] = $stat['last_run'] ?? null;
}

function e_admin_bot($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function bot_last_run($value) {
    return !empty($value) ? date('H:i d/m/Y', strtotime($value)) : 'Chưa có dữ liệu';
}

$bots = [
    [
        'type' => 'tiki',
        'name' => 'Tiki Scraper',
        'platform' => 'Tiki',
        'desc' => 'Lấy giá, URL ảnh và trạng thái sản phẩm qua API Tiki.',
        'logo' => 'https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png',
        'tone' => 'tiki',
        'icon' => 'fa-cloud-arrow-down',
    ],
    [
        'type' => 'shopee',
        'name' => 'Shopee Crawler',
        'platform' => 'Shopee',
        'desc' => 'Cập nhật giá từ link Shopee đang hoạt động trong hệ thống.',
        'logo' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg',
        'tone' => 'shopee',
        'icon' => 'fa-cart-shopping',
    ],
    [
        'type' => 'lazada',
        'name' => 'Lazada Crawler',
        'platform' => 'Lazada',
        'desc' => 'Quét giá Lazada và lưu lại lịch sử biến động theo thời gian.',
        'logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png',
        'tone' => 'lazada',
        'icon' => 'fa-tags',
    ],
    [
        'type' => 'matcher',
        'name' => 'Fuzzy Matcher',
        'platform' => null,
        'desc' => 'Tìm link tương ứng trên nhiều sàn dựa theo tên sản phẩm đã có.',
        'logo' => '',
        'tone' => 'matcher',
        'icon' => 'fa-wand-magic-sparkles',
    ],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bot - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bot-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .bot-card {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .bot-card-top {
            height: 6px;
            background: #64748b;
        }

        .bot-card-top.tiki { background: #1a94ff; }
        .bot-card-top.shopee { background: #ee4d2d; }
        .bot-card-top.lazada { background: #1a237e; }
        .bot-card-top.matcher { background: #111827; }

        .bot-logo-wrap {
            width: 68px;
            height: 68px;
            border-radius: 12px;
            border: 1px solid #e4e7ec;
            display: grid;
            place-items: center;
            background: #fff;
            margin-bottom: 16px;
        }

        .bot-logo-wrap img {
            max-width: 50px;
            max-height: 34px;
            object-fit: contain;
        }

        .bot-logo-wrap i {
            color: #111827;
            font-size: 1.6rem;
        }

        .terminal-panel {
            background: #0f172a;
            color: #d1fae5;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #1e293b;
            box-shadow: 0 18px 32px rgba(15,23,42,.16);
            margin-bottom: 20px;
        }

        .terminal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #111827;
            border-bottom: 1px solid #1f2937;
            color: #fff;
            font-weight: 800;
        }

        .terminal-body {
            max-height: 360px;
            overflow: auto;
            padding: 16px;
            font-family: Consolas, Monaco, monospace;
            font-size: .88rem;
        }

        .terminal-body pre {
            white-space: pre-wrap;
            margin-bottom: 0;
            color: inherit;
        }

        .guide-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .guide-item {
            padding: 16px;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            background: #fff;
        }

        .task-warning {
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: #9a3412;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        .task-warning pre {
            margin: 8px 0 0;
            white-space: pre-wrap;
            color: inherit;
            font-size: .82rem;
            font-weight: 600;
        }

        .bot-alert-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
            font-size: .78rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        @media (max-width: 1199px) {
            .bot-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767px) {
            .bot-grid,
            .guide-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="admin-page-head">
            <div>
                <div class="admin-page-kicker">Automation</div>
                <h1 class="admin-page-title">Trung tâm điều khiển bot</h1>
                <p class="admin-page-desc">Chạy crawler, cập nhật giá và kiểm tra đầu ra của các script Python.</p>
            </div>
        </div>

        <?php if (isset($_SESSION['bot_message'])):
            $status = $_SESSION['bot_status'] ?? 'info';
            $statusText = [
                'success' => 'Hoàn tất',
                'error' => 'Có lỗi',
                'warning' => 'Cần kiểm tra',
                'info' => 'Thông tin',
            ][$status] ?? 'Thông tin';
        ?>
            <section class="terminal-panel">
                <div class="terminal-head">
                    <span><i class="fas fa-terminal me-2"></i>Kết quả chạy gần nhất</span>
                    <span class="badge rounded-pill text-bg-<?php echo $status === 'error' ? 'danger' : ($status === 'success' ? 'success' : 'warning'); ?>">
                        <?php echo e_admin_bot($statusText); ?>
                    </span>
                </div>
                <div class="terminal-body">
                    <?php echo $_SESSION['bot_message']; ?>
                </div>
            </section>
        <?php
            unset($_SESSION['bot_message'], $_SESSION['bot_status']);
        endif;
        ?>

        <?php if (!empty($botTaskWarnings)): ?>
            <section class="task-warning">
                <div><i class="fas fa-shield-halved me-2"></i>Có bot tự động đã bị tắt do captcha/block.</div>
                <div class="small mt-1">Hãy chạy bot thủ công để xử lý xác minh, sau đó bật lại Task Scheduler cho sàn tương ứng.</div>
                <?php foreach ($botTaskWarnings as $type => $warning): ?>
                    <pre><?php echo e_admin_bot(strtoupper($type) . ' - ' . ($warning['updated_at'] ?? '') . "\n" . ($warning['message'] ?? '')); ?></pre>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section class="bot-grid">
            <?php foreach ($bots as $bot):
                $lastRun = $bot['platform'] ? ($lastRunByPlatform[$bot['platform']] ?? null) : null;
                $taskWarning = $botTaskWarnings[$bot['type']] ?? null;
            ?>
                <article class="admin-card bot-card">
                    <div class="bot-card-top <?php echo e_admin_bot($bot['tone']); ?>"></div>
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="bot-logo-wrap">
                            <?php if (!empty($bot['logo'])): ?>
                                <img src="<?php echo e_admin_bot($bot['logo']); ?>" alt="<?php echo e_admin_bot($bot['name']); ?>">
                            <?php else: ?>
                                <i class="fas <?php echo e_admin_bot($bot['icon']); ?>"></i>
                            <?php endif; ?>
                        </div>
                        <h2 class="h5 fw-bold mb-2"><?php echo e_admin_bot($bot['name']); ?></h2>
                        <?php if ($taskWarning): ?>
                            <div class="bot-alert-badge">
                                <i class="fas fa-triangle-exclamation"></i>
                                Tự động đã tắt do captcha/block
                            </div>
                        <?php endif; ?>
                        <p class="text-muted small mb-3"><?php echo e_admin_bot($bot['desc']); ?></p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between gap-3 border-top pt-3 mb-3">
                                <span class="text-muted small">Lần quét gần nhất</span>
                                <strong class="small text-end"><?php echo e_admin_bot($bot['platform'] ? bot_last_run($lastRun) : 'Chạy theo yêu cầu'); ?></strong>
                            </div>
                            <a href="index.php?role=admin&controller=bot&action=run&type=<?php echo e_admin_bot($bot['type']); ?>"
                               class="btn btn-admin-primary w-100 bot-run-button">
                                <i class="fas fa-play me-2"></i>Chạy bot
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-card p-4 mt-4">
            <h2 class="h5 fw-bold mb-3">Ghi chú vận hành</h2>
            <div class="guide-grid">
                <div class="guide-item">
                    <div class="fw-bold mb-1"><i class="fas fa-link text-primary me-2"></i>Link dữ liệu</div>
                    <div class="text-muted small">Thêm link sàn cho sản phẩm trước khi chạy crawler để bot có nguồn cập nhật.</div>
                </div>
                <div class="guide-item">
                    <div class="fw-bold mb-1"><i class="fas fa-clock text-primary me-2"></i>Lịch chạy</div>
                    <div class="text-muted small">Có thể tạo lịch tự động bằng <code>scripts/create_windows_scheduled_tasks.bat</code>; Shopee chạy từng batch nhỏ để giảm captcha.</div>
                </div>
                <div class="guide-item">
                    <div class="fw-bold mb-1"><i class="fas fa-triangle-exclamation text-primary me-2"></i>Khi bị chặn</div>
                    <div class="text-muted small">Nếu script báo captcha hoặc đăng nhập, hãy chạy trực tiếp trong terminal để xử lý thủ công.</div>
                </div>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.bot-run-button').forEach((button) => {
        button.addEventListener('click', () => {
            button.classList.add('disabled');
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang chạy...';
        });
    });
</script>
</body>
</html>
