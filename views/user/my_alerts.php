<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e_alerts($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money_alerts($value) {
    if (!$value || (float) $value <= 0) {
        return 'Đang cập nhật';
    }

    return number_format((float) $value, 0, ',', '.') . 'đ';
}

$alerts = $alerts ?? [];
$totalAlerts = count($alerts);
$readyAlerts = 0;
$waitingAlerts = 0;

foreach ($alerts as $alertItem) {
    $minPrice = (float) ($alertItem['min_price'] ?? 0);
    $targetPrice = (float) ($alertItem['target_price'] ?? 0);
    if ($minPrice > 0 && $targetPrice > 0 && $minPrice <= $targetPrice) {
        $readyAlerts++;
    } else {
        $waitingAlerts++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách săn sale - Price Comparison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --ink: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --soft: #f4f6f8;
            --brand: #f7c600;
            --danger: #e11d48;
            --success: #16a34a;
            --blue: #2563eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--soft);
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .topbar {
            background: #0f172a;
            color: #fff;
            border-bottom: 4px solid var(--brand);
        }

        .topbar-inner {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-weight: 900;
            letter-spacing: 0;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--brand);
            color: #111827;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .pill-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            color: #e5e7eb;
            border: 1px solid rgba(255,255,255,.18);
            text-decoration: none;
            font-weight: 700;
            font-size: .92rem;
        }

        .pill-link.primary {
            background: var(--brand);
            color: #111827;
            border-color: var(--brand);
        }

        .hero {
            background: linear-gradient(135deg, #111827 0%, #1e293b 58%, #334155 100%);
            color: #fff;
            padding: 34px 0;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 24px;
            align-items: stretch;
        }

        .eyebrow {
            color: var(--brand);
            font-weight: 900;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: 0;
            margin-bottom: 8px;
        }

        .hero h1 {
            font-size: clamp(1.8rem, 3vw, 3rem);
            line-height: 1.08;
            font-weight: 950;
            margin: 0 0 12px;
            letter-spacing: 0;
        }

        .hero p {
            color: #cbd5e1;
            max-width: 700px;
            margin: 0;
            font-size: 1.02rem;
        }

        .summary-panel {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 8px;
            padding: 18px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .summary-item {
            background: rgba(255,255,255,.08);
            border-radius: 8px;
            padding: 14px;
        }

        .summary-number {
            display: block;
            font-size: 1.6rem;
            font-weight: 950;
            line-height: 1;
        }

        .summary-label {
            display: block;
            margin-top: 6px;
            color: #dbeafe;
            font-size: .78rem;
            font-weight: 700;
        }

        .page-shell {
            padding: 28px 0 44px;
        }

        .notice {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            border-radius: 8px;
            padding: 12px 14px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .toolbar h2 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 950;
        }

        .toolbar p {
            margin: 3px 0 0;
            color: var(--muted);
        }

        .quick-search {
            display: flex;
            min-width: 340px;
            max-width: 460px;
            flex: 1;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15,23,42,.06);
        }

        .quick-search input {
            min-width: 0;
            border: 0;
            flex: 1;
            padding: 0 14px;
            outline: none;
        }

        .quick-search button {
            border: 0;
            background: var(--brand);
            color: #111827;
            width: 48px;
            font-weight: 900;
        }

        .alert-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .watch-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(15,23,42,.07);
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .watch-media {
            position: relative;
            aspect-ratio: 1 / .82;
            background: #fff;
            border-bottom: 1px solid var(--line);
            display: grid;
            place-items: center;
            padding: 16px;
        }

        .watch-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .empty-product-icon {
            color: #cbd5e1;
            font-size: 4rem;
        }

        .status-badge {
            position: absolute;
            left: 12px;
            top: 12px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .76rem;
            font-weight: 900;
            box-shadow: 0 8px 18px rgba(15,23,42,.14);
        }

        .status-badge.ready {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.waiting {
            background: #fff7ed;
            color: #9a3412;
        }

        .watch-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-title {
            color: var(--ink);
            text-decoration: none;
            font-weight: 850;
            line-height: 1.35;
            min-height: 46px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-title:hover { color: var(--blue); }

        .price-panel {
            margin: 14px 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            background: #f8fafc;
        }

        .price-row + .price-row {
            border-top: 1px solid var(--line);
            background: #fff;
        }

        .price-label {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 800;
        }

        .target-price {
            color: var(--danger);
            font-size: 1.05rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .current-price {
            color: var(--ink);
            font-size: 1.02rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .current-price.ready {
            color: var(--success);
        }

        .watch-meta {
            color: var(--muted);
            font-size: .8rem;
            margin-bottom: 14px;
        }

        .watch-actions {
            display: grid;
            grid-template-columns: 1fr 44px;
            gap: 8px;
            margin-top: auto;
        }

        .btn-main {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border-radius: 8px;
            border: 1px solid #111827;
            background: #111827;
            color: #fff;
            text-decoration: none;
            font-weight: 850;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border-radius: 8px;
            border: 1px solid #fecdd3;
            color: var(--danger);
            background: #fff1f2;
            text-decoration: none;
        }

        .empty-state {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 48px 24px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(15,23,42,.07);
        }

        .empty-state i {
            width: 82px;
            height: 82px;
            display: inline-grid;
            place-items: center;
            border-radius: 20px;
            background: #fff7ed;
            color: #ea580c;
            font-size: 2.1rem;
            margin-bottom: 18px;
        }

        .empty-state h3 {
            font-weight: 950;
            margin-bottom: 8px;
        }

        .empty-state p {
            max-width: 520px;
            margin: 0 auto 22px;
            color: var(--muted);
        }

        .footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 22px 0;
            margin-top: auto;
            font-size: .9rem;
        }

        @media (max-width: 1199px) {
            .alert-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .hero-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 991px) {
            .alert-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .toolbar { align-items: stretch; flex-direction: column; }
            .quick-search { min-width: 0; max-width: none; width: 100%; }
        }

        @media (max-width: 575px) {
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .top-actions { width: 100%; justify-content: flex-start; }
            .pill-link { flex: 1; min-width: 140px; }
            .summary-panel { grid-template-columns: 1fr; }
            .alert-grid { grid-template-columns: 1fr; }
            .hero { padding: 26px 0; }
        }
    </style>
</head>
<body class="d-flex flex-column">
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand-link" href="index.php">
                <span class="brand-mark"><i class="fas fa-bolt"></i></span>
                <span>Price Comparison</span>
            </a>
            <div class="top-actions">
                <a class="pill-link" href="index.php"><i class="fas fa-house"></i> Trang chủ</a>
                <a class="pill-link primary" href="index.php"><i class="fas fa-magnifying-glass"></i> Tìm deal mới</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="eyebrow">Trung tâm săn sale</div>
                <h1>Sản phẩm đang theo dõi</h1>
                <p>Theo dõi mức giá mong muốn của bạn và quay lại sản phẩm ngay khi giá rẻ nhất trên các sàn đã sẵn sàng để chốt.</p>
            </div>
            <div class="summary-panel" aria-label="Thống kê cảnh báo giá">
                <div class="summary-item">
                    <span class="summary-number"><?php echo number_format($totalAlerts); ?></span>
                    <span class="summary-label">Đang theo dõi</span>
                </div>
                <div class="summary-item">
                    <span class="summary-number"><?php echo number_format($readyAlerts); ?></span>
                    <span class="summary-label">Đạt giá</span>
                </div>
                <div class="summary-item">
                    <span class="summary-number"><?php echo number_format($waitingAlerts); ?></span>
                    <span class="summary-label">Đang canh</span>
                </div>
            </div>
        </div>
    </section>

    <main class="page-shell flex-grow-1">
        <div class="container">
            <?php if (($_GET['msg'] ?? '') === 'alert_removed'): ?>
                <div class="notice"><i class="fas fa-check-circle me-2"></i>Đã hủy theo dõi sản phẩm.</div>
            <?php endif; ?>

            <div class="toolbar">
                <div>
                    <h2>Danh sách của bạn</h2>
                    <p><?php echo $totalAlerts > 0 ? 'Các sản phẩm được sắp theo thời gian thiết lập mới nhất.' : 'Bạn chưa có sản phẩm nào trong danh sách theo dõi.'; ?></p>
                </div>
                <form class="quick-search" action="index.php" method="GET">
                    <input type="hidden" name="role" value="user">
                    <input type="hidden" name="controller" value="product">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="keyword" placeholder="Tìm thêm điện thoại, laptop, phụ kiện...">
                    <button type="submit" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <?php if (empty($alerts)): ?>
                <section class="empty-state">
                    <i class="fas fa-bell"></i>
                    <h3>Chưa có sản phẩm theo dõi</h3>
                    <p>Hãy tìm sản phẩm, mở trang chi tiết và đặt mức giá mong muốn. Danh sách này sẽ giúp bạn kiểm tra nhanh sản phẩm nào đã chạm giá.</p>
                    <a class="btn-main px-4" href="index.php"><i class="fas fa-bolt"></i> Khám phá deal</a>
                </section>
            <?php else: ?>
                <div class="alert-grid">
                    <?php foreach ($alerts as $item):
                        $productId = (int) ($item['product_id'] ?? 0);
                        $productName = $item['product_name'] ?? 'Sản phẩm';
                        $minPrice = (float) ($item['min_price'] ?? 0);
                        $targetPrice = (float) ($item['target_price'] ?? 0);
                        $isReached = ($minPrice > 0 && $targetPrice > 0 && $minPrice <= $targetPrice);
                        $createdAt = !empty($item['alert_created_at']) ? date('d/m/Y', strtotime($item['alert_created_at'])) : 'Chưa rõ ngày';
                    ?>
                        <article class="watch-card">
                            <div class="watch-media">
                                <span class="status-badge <?php echo $isReached ? 'ready' : 'waiting'; ?>">
                                    <i class="fas <?php echo $isReached ? 'fa-circle-check' : 'fa-clock'; ?> me-1"></i>
                                    <?php echo $isReached ? 'Đạt giá' : 'Đang canh'; ?>
                                </span>
                                <?php if (!empty($item['thumbnail_url'])): ?>
                                    <img src="<?php echo e_alerts($item['thumbnail_url']); ?>" alt="<?php echo e_alerts($productName); ?>">
                                <?php else: ?>
                                    <i class="fas fa-box-open empty-product-icon"></i>
                                <?php endif; ?>
                            </div>
                            <div class="watch-body">
                                <a class="product-title" href="index.php?role=user&controller=product&action=detail&id=<?php echo $productId; ?>" title="<?php echo e_alerts($productName); ?>">
                                    <?php echo e_alerts($productName); ?>
                                </a>

                                <div class="price-panel">
                                    <div class="price-row">
                                        <span class="price-label">Giá mong muốn</span>
                                        <span class="target-price"><?php echo money_alerts($targetPrice); ?></span>
                                    </div>
                                    <div class="price-row">
                                        <span class="price-label">Rẻ nhất hiện tại</span>
                                        <span class="current-price <?php echo $isReached ? 'ready' : ''; ?>"><?php echo money_alerts($minPrice); ?></span>
                                    </div>
                                </div>

                                <div class="watch-meta">
                                    <i class="fas fa-calendar-check me-1"></i> Theo dõi từ <?php echo e_alerts($createdAt); ?>
                                </div>

                                <div class="watch-actions">
                                    <a class="btn-main" href="index.php?role=user&controller=product&action=detail&id=<?php echo $productId; ?>">
                                        <i class="fas fa-chart-line"></i> Xem giá
                                    </a>
                                    <a class="btn-icon" href="index.php?role=user&controller=product&action=removeAlert&id=<?php echo $productId; ?>&redirect=my_alerts"
                                       onclick="return confirm('Bạn muốn hủy theo dõi sản phẩm này?');"
                                       aria-label="Hủy theo dõi">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; <?php echo date('Y'); ?> Price Comparison</span>
            <span>So sánh giá đa sàn, theo dõi biến động và cảnh báo deal.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
