<?php
$stats = $stats ?? [
    'total_products' => 0,
    'total_categories' => 0,
    'total_alerts' => 0,
    'total_users' => 0,
];

$statCards = [
    [
        'label' => 'Sản phẩm',
        'value' => $stats['total_products'] ?? 0,
        'icon' => 'fa-box',
        'tone' => 'blue',
        'desc' => 'Đang quản lý trong hệ thống',
    ],
    [
        'label' => 'Danh mục',
        'value' => $stats['total_categories'] ?? 0,
        'icon' => 'fa-layer-group',
        'tone' => 'green',
        'desc' => 'Nhóm sản phẩm đang hiển thị',
    ],
    [
        'label' => 'Cảnh báo giá',
        'value' => $stats['total_alerts'] ?? 0,
        'icon' => 'fa-bell',
        'tone' => 'amber',
        'desc' => 'Lượt theo dõi của người dùng',
    ],
    [
        'label' => 'Người dùng',
        'value' => $stats['total_users'] ?? 0,
        'icon' => 'fa-users',
        'tone' => 'rose',
        'desc' => 'Tài khoản đã xác thực',
    ],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            padding: 22px;
            min-height: 156px;
        }

        .metric-card::after {
            content: "";
            position: absolute;
            width: 130px;
            height: 130px;
            right: -46px;
            bottom: -54px;
            border-radius: 999px;
            opacity: .14;
            background: currentColor;
        }

        .metric-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .metric-label {
            color: #667085;
            font-weight: 850;
            font-size: .88rem;
        }

        .metric-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: #fff;
            flex: 0 0 auto;
        }

        .metric-value {
            font-size: 2.05rem;
            line-height: 1;
            font-weight: 950;
            margin-bottom: 8px;
        }

        .metric-desc {
            color: #667085;
            font-size: .9rem;
        }

        .tone-blue { color: #2563eb; }
        .tone-green { color: #16a34a; }
        .tone-amber { color: #d97706; }
        .tone-rose { color: #e11d48; }
        .tone-blue .metric-icon { background: #2563eb; }
        .tone-green .metric-icon { background: #16a34a; }
        .tone-amber .metric-icon { background: #d97706; }
        .tone-rose .metric-icon { background: #e11d48; }

        .admin-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(300px, .7fr);
            gap: 18px;
            margin-top: 20px;
        }

        .action-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .action-tile {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 82px;
            padding: 16px;
            color: #101828;
            text-decoration: none;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            background: #fff;
            transition: border-color .18s ease, transform .18s ease;
        }

        .action-tile:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
        }

        .action-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #eff6ff;
            color: #2563eb;
            flex: 0 0 auto;
        }

        .action-title {
            display: block;
            font-weight: 900;
            line-height: 1.2;
        }

        .action-desc {
            display: block;
            color: #667085;
            font-size: .84rem;
            margin-top: 3px;
        }

        .ops-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .ops-row:last-child { border-bottom: 0; }

        @media (max-width: 1199px) {
            .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .admin-workspace { grid-template-columns: 1fr; }
        }

        @media (max-width: 575px) {
            .metric-grid,
            .action-list {
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
                <div class="admin-page-kicker">Dashboard</div>
                <h1 class="admin-page-title">Tổng quan hệ thống</h1>
                <p class="admin-page-desc">Theo dõi nhanh dữ liệu sản phẩm, người dùng, bot và cảnh báo giá.</p>
            </div>
            <a class="btn btn-admin-primary px-4" href="index.php?role=admin&controller=bot&action=index">
                <i class="fas fa-robot me-2"></i>Quản lý bot
            </a>
        </div>

        <section class="metric-grid">
            <?php foreach ($statCards as $card): ?>
                <article class="admin-card metric-card tone-<?php echo htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="metric-top">
                        <span class="metric-label"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="metric-icon"><i class="fas <?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                    </div>
                    <div class="metric-value"><?php echo number_format((float) $card['value'], 0, ',', '.'); ?></div>
                    <div class="metric-desc"><?php echo htmlspecialchars($card['desc'], ENT_QUOTES, 'UTF-8'); ?></div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-workspace">
            <div class="admin-card p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Tác vụ nhanh</h2>
                        <p class="text-muted mb-0">Các khu vực thường dùng khi vận hành dữ liệu giá.</p>
                    </div>
                </div>
                <div class="action-list">
                    <a class="action-tile" href="index.php?role=admin&controller=adminProduct&action=index">
                        <span class="action-icon"><i class="fas fa-box"></i></span>
                        <span>
                            <span class="action-title">Quản lý sản phẩm</span>
                            <span class="action-desc">Thêm, sửa và gắn danh mục</span>
                        </span>
                    </a>
                    <a class="action-tile" href="index.php?role=admin&controller=adminCategory&action=index">
                        <span class="action-icon"><i class="fas fa-layer-group"></i></span>
                        <span>
                            <span class="action-title">Danh mục</span>
                            <span class="action-desc">Chuẩn hóa nhóm sản phẩm</span>
                        </span>
                    </a>
                    <a class="action-tile" href="index.php?role=admin&controller=bot&action=index">
                        <span class="action-icon"><i class="fas fa-play"></i></span>
                        <span>
                            <span class="action-title">Chạy bot</span>
                            <span class="action-desc">Cập nhật giá và link sàn</span>
                        </span>
                    </a>
                    <a class="action-tile" href="index.php?role=admin&controller=dashboard&action=alerts">
                        <span class="action-icon"><i class="fas fa-bell"></i></span>
                        <span>
                            <span class="action-title">Cảnh báo giá</span>
                            <span class="action-desc">Theo dõi nhu cầu người dùng</span>
                        </span>
                    </a>
                </div>
            </div>

            <aside class="admin-card p-4">
                <h2 class="h5 fw-bold mb-1">Tình trạng vận hành</h2>
                <p class="text-muted mb-3">Các chỉ số nên kiểm tra định kỳ.</p>
                <div class="ops-row">
                    <span class="text-muted">Sản phẩm có dữ liệu</span>
                    <strong><?php echo number_format((float) ($stats['total_products'] ?? 0), 0, ',', '.'); ?></strong>
                </div>
                <div class="ops-row">
                    <span class="text-muted">Cảnh báo cần theo dõi</span>
                    <strong><?php echo number_format((float) ($stats['total_alerts'] ?? 0), 0, ',', '.'); ?></strong>
                </div>
                <div class="ops-row">
                    <span class="text-muted">Người dùng xác thực</span>
                    <strong><?php echo number_format((float) ($stats['total_users'] ?? 0), 0, ',', '.'); ?></strong>
                </div>
            </aside>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
