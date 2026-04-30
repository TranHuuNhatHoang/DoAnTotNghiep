<?php
$alerts = $alerts ?? [];

function e_admin_alert($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money_admin_alert($value) {
    if (!$value || (float) $value <= 0) {
        return 'Đang chờ quét';
    }

    return number_format((float) $value, 0, ',', '.') . 'đ';
}

$readyCount = 0;
$sentCount = 0;
foreach ($alerts as $alert) {
    $currentPrice = (float) ($alert['current_min_price'] ?? 0);
    $targetPrice = (float) ($alert['target_price'] ?? 0);
    if ($currentPrice > 0 && $targetPrice > 0 && $currentPrice <= $targetPrice) {
        $readyCount++;
    }
    if ((int) ($alert['is_notified'] ?? 0) === 1) {
        $sentCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý cảnh báo giá - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .alert-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .summary-card {
            padding: 18px;
        }

        .summary-card span {
            display: block;
            color: #667085;
            font-size: .85rem;
            font-weight: 850;
        }

        .summary-card strong {
            display: block;
            margin-top: 6px;
            font-size: 1.8rem;
            line-height: 1;
            font-weight: 950;
        }

        .price-ready { color: #16a34a; }
        .price-wait { color: #e11d48; }

        @media (max-width: 767px) {
            .alert-summary { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="admin-page-head">
            <div>
                <div class="admin-page-kicker">Price alerts</div>
                <h1 class="admin-page-title">Cảnh báo giá</h1>
                <p class="admin-page-desc">Theo dõi nhu cầu săn sale và trạng thái gửi email cho người dùng.</p>
            </div>
        </div>

        <section class="alert-summary">
            <article class="admin-card summary-card">
                <span>Tổng yêu cầu</span>
                <strong><?php echo number_format(count($alerts), 0, ',', '.'); ?></strong>
            </article>
            <article class="admin-card summary-card">
                <span>Đã chạm giá</span>
                <strong class="price-ready"><?php echo number_format($readyCount, 0, ',', '.'); ?></strong>
            </article>
            <article class="admin-card summary-card">
                <span>Đã gửi email</span>
                <strong><?php echo number_format($sentCount, 0, ',', '.'); ?></strong>
            </article>
        </section>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 70px;">#</th>
                            <th>Khách hàng</th>
                            <th style="min-width: 280px;">Sản phẩm</th>
                            <th class="text-end">Giá mong muốn</th>
                            <th class="text-end">Giá rẻ nhất</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-4">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alerts)): ?>
                            <?php foreach ($alerts as $index => $alert):
                                $currentPrice = (float) ($alert['current_min_price'] ?? 0);
                                $targetPrice = (float) ($alert['target_price'] ?? 0);
                                $isReady = $currentPrice > 0 && $targetPrice > 0 && $currentPrice <= $targetPrice;
                                $isSent = (int) ($alert['is_notified'] ?? 0) === 1;
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted"><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo e_admin_alert($alert['email'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <a href="index.php?role=user&controller=product&action=detail&id=<?php echo (int) ($alert['p_id'] ?? 0); ?>"
                                           target="_blank"
                                           class="fw-bold text-decoration-none line-clamp-2"
                                           title="<?php echo e_admin_alert($alert['product_name'] ?? ''); ?>">
                                            <?php echo e_admin_alert($alert['product_name'] ?? 'Sản phẩm'); ?>
                                        </a>
                                    </td>
                                    <td class="text-end fw-bold"><?php echo e_admin_alert(money_admin_alert($targetPrice)); ?></td>
                                    <td class="text-end fw-bold <?php echo $isReady ? 'price-ready' : 'price-wait'; ?>">
                                        <?php echo e_admin_alert(money_admin_alert($currentPrice)); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isSent): ?>
                                            <span class="badge rounded-pill text-bg-success">Đã gửi email</span>
                                        <?php elseif ($isReady): ?>
                                            <span class="badge rounded-pill text-bg-warning">Sẵn sàng gửi</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill text-bg-secondary">Đang canh giá</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 text-muted">
                                        <?php echo !empty($alert['created_at']) ? date('d/m/Y H:i', strtotime($alert['created_at'])) : 'Chưa rõ'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-bell-slash fa-2x mb-3 d-block"></i>
                                    Chưa có yêu cầu cảnh báo giá.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
