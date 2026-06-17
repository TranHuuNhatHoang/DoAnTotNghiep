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
$buyAdviceAlerts = 0;
$waitAdviceAlerts = 0;
$buyAdviceItems = [];
$waitAdviceItems = [];

foreach ($alerts as $alertItem) {
    $minPrice = (float) ($alertItem['min_price'] ?? 0);
    $targetPrice = (float) ($alertItem['target_price'] ?? 0);
    if ($minPrice > 0 && $targetPrice > 0 && $minPrice <= $targetPrice) {
        $readyAlerts++;
    } else {
        $waitingAlerts++;
    }

    $adviceCode = $alertItem['price_analysis']['recommendation']['code'] ?? '';
    if ($adviceCode === 'buy') {
        $buyAdviceAlerts++;
        $buyAdviceItems[] = $alertItem;
    } elseif ($adviceCode === 'wait') {
        $waitAdviceAlerts++;
        $waitAdviceItems[] = $alertItem;
    }
}

$readyPercent = $totalAlerts > 0 ? (int) round(($readyAlerts / $totalAlerts) * 100) : 0;
$actionSummary = $readyAlerts > 0
    ? $readyAlerts . ' sản phẩm đã chạm mức giá bạn đặt.'
    : 'Chưa có sản phẩm nào chạm mức giá mong muốn.';

function advice_badge_class($code) {
    return [
        'buy' => 'ready',
        'consider' => 'consider',
        'wait' => 'waiting',
    ][$code] ?? 'muted';
}

function dashboard_action_item($item) {
    $productId = (int) ($item['product_id'] ?? 0);
    $name = $item['product_name'] ?? 'Sản phẩm';
    $analysis = $item['price_analysis'] ?? [];
    $reason = $analysis['recommendation']['reason'] ?? ($analysis['recommendation']['message'] ?? 'Đang phân tích dữ liệu giá.');
    $trend = $analysis['trend']['label'] ?? 'Đang cập nhật';
    $price = money_alerts($item['min_price'] ?? 0);

    return '<a class="decision-item" href="index.php?role=user&controller=product&action=detail&id=' . $productId . '">' .
        '<span><strong>' . e_alerts($name) . '</strong><small>' . e_alerts($reason) . '</small><small>Xu hướng: ' . e_alerts($trend) . '</small></span>' .
        '<b>' . e_alerts($price) . '</b>' .
    '</a>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard giá của tôi - SmartPrice</title>
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
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
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

        .decision-board {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }

        .decision-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 12px 28px rgba(15,23,42,.07);
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
            overflow: hidden;
        }

        .decision-head {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 0;
            padding: 18px;
            border-right: 1px solid var(--line);
            background: #f8fafc;
        }

        .decision-head h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 950;
        }

        .decision-count {
            min-width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-grid;
            place-items: center;
            font-weight: 950;
            background: #f1f5f9;
        }

        .decision-panel.buy .decision-count { background: #dcfce7; color: #166534; }
        .decision-panel.wait .decision-count { background: #fff7ed; color: #9a3412; }

        .decision-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            padding: 14px;
        }

        .decision-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 11px;
            border-radius: 8px;
            border: 1px solid #edf2f7;
            background: #f8fafc;
            color: var(--ink);
            text-decoration: none;
        }

        .decision-item:hover {
            border-color: var(--blue);
        }

        .decision-item strong {
            display: block;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .decision-item small {
            display: block;
            color: var(--muted);
            font-weight: 700;
            line-height: 1.35;
        }

        .decision-item b {
            color: var(--danger);
            white-space: nowrap;
        }

        .decision-empty {
            color: var(--muted);
            background: #f8fafc;
            border: 1px dashed #d8dee8;
            border-radius: 8px;
            padding: 12px;
            font-size: .9rem;
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

        .advice-box {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            background: #f8fafc;
        }

        .advice-box.ready {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .advice-box.waiting {
            border-color: #fed7aa;
            background: #fff7ed;
            color: #9a3412;
        }

        .advice-box.consider {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .advice-box.muted {
            color: var(--muted);
        }

        .advice-title {
            display: flex;
            align-items: center;
            gap: 7px;
            font-weight: 950;
            font-size: .84rem;
            margin-bottom: 3px;
        }

        .advice-text {
            font-size: .78rem;
            font-weight: 700;
            color: inherit;
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

        .dashboard-hero {
            background:
                radial-gradient(circle at 86% 18%, rgba(247,198,0,.22), transparent 24%),
                linear-gradient(135deg, #0f172a 0%, #172033 58%, #26364f 100%);
            padding: 34px 0 28px;
        }

        .dashboard-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 20px;
            align-items: stretch;
        }

        .hero-copy-card,
        .hero-status-card {
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 8px;
            background: rgba(255,255,255,.08);
            box-shadow: 0 18px 44px rgba(2,6,23,.22);
        }

        .hero-copy-card {
            padding: 28px;
        }

        .hero-copy-card h1 {
            max-width: 760px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .hero-action {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 14px;
            border-radius: 8px;
            color: #111827;
            background: var(--brand);
            text-decoration: none;
            font-weight: 900;
        }

        .hero-action.secondary {
            color: #fff;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
        }

        .hero-status-card {
            padding: 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
        }

        .status-kicker {
            color: #cbd5e1;
            font-size: .82rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .status-main {
            display: block;
            color: #fff;
            font-size: 1.65rem;
            line-height: 1.15;
            font-weight: 950;
        }

        .status-desc {
            margin: 8px 0 0;
            color: #cbd5e1;
            line-height: 1.5;
            font-weight: 650;
        }

        .progress-track {
            height: 10px;
            border-radius: 999px;
            background: rgba(255,255,255,.15);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #22c55e, #f7c600);
        }

        .dashboard-metrics {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 18px;
            padding: 0;
            background: transparent;
            border: 0;
        }

        .dashboard-metrics .summary-item {
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 12px 28px rgba(15,23,42,.08);
            min-height: 92px;
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr);
            grid-template-areas:
                "icon number"
                "icon label";
            align-items: center;
            column-gap: 12px;
            padding: 16px;
        }

        .dashboard-metrics .summary-icon {
            grid-area: icon;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            margin-bottom: 0;
            background: #eef4ff;
            color: var(--blue);
            font-size: 1rem;
        }

        .dashboard-metrics .summary-number {
            grid-area: number;
            color: var(--ink);
            font-size: 2rem;
            font-weight: 950;
            line-height: 1;
        }

        .dashboard-metrics .summary-label {
            grid-area: label;
            color: var(--muted);
            margin-top: 4px;
            font-size: .82rem;
            font-weight: 850;
        }

        .dashboard-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin: 26px 0 14px;
        }

        .dashboard-section-head h2 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 950;
        }

        .dashboard-section-head p {
            margin: 4px 0 0;
            color: var(--muted);
            font-weight: 650;
        }

        .decision-panel {
            border-radius: 8px;
            border-left: 4px solid #dbe4ef;
        }

        .decision-panel.buy { border-left-color: #22c55e; }
        .decision-panel.wait { border-left-color: #f97316; }

        .decision-item {
            min-height: 74px;
            background: #fff;
        }

        .decision-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15,23,42,.08);
        }

        .toolbar.dashboard-toolbar {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 12px 28px rgba(15,23,42,.06);
            justify-content: flex-start;
        }

        .alert-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .watch-card {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr);
            min-height: 0;
        }

        .watch-media {
            aspect-ratio: auto;
            min-height: 100%;
            border-right: 1px solid var(--line);
            border-bottom: 0;
            background: #f8fafc;
        }

        .watch-body {
            min-width: 0;
        }

        .price-panel {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .price-row + .price-row {
            border-top: 0;
            border-left: 1px solid var(--line);
        }

        .advice-box {
            min-height: 92px;
        }

        .watch-actions {
            grid-template-columns: minmax(0, 1fr) 44px;
        }

        @media (max-width: 1199px) {
            .alert-grid { grid-template-columns: 1fr; }
            .hero-grid,
            .dashboard-hero-grid { grid-template-columns: 1fr; }
            .dashboard-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 991px) {
            .decision-board { grid-template-columns: 1fr; }
            .decision-panel { grid-template-columns: 1fr; }
            .decision-head { border-right: 0; border-bottom: 1px solid var(--line); }
            .decision-list { grid-template-columns: 1fr; }
            .toolbar,
            .dashboard-section-head { align-items: stretch; flex-direction: column; }
            .dashboard-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 575px) {
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .top-actions { width: 100%; justify-content: flex-start; }
            .pill-link { flex: 1; min-width: 140px; }
            .summary-panel,
            .dashboard-metrics { grid-template-columns: 1fr; }
            .alert-grid { grid-template-columns: 1fr; }
            .watch-card { grid-template-columns: 1fr; }
            .watch-media { min-height: 210px; border-right: 0; border-bottom: 1px solid var(--line); }
            .price-panel { grid-template-columns: 1fr; }
            .price-row + .price-row { border-left: 0; border-top: 1px solid var(--line); }
            .hero { padding: 26px 0; }
            .hero-copy-card { padding: 22px; }
        }
    </style>
</head>
<body class="d-flex flex-column">
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand-link" href="index.php">
                <span class="brand-mark"><i class="fas fa-bolt"></i></span>
                <span>SmartPrice</span>
            </a>
            <div class="top-actions">
                <a class="pill-link" href="index.php"><i class="fas fa-house"></i> Trang chủ</a>
                <a class="pill-link primary" href="index.php"><i class="fas fa-magnifying-glass"></i> Tìm deal mới</a>
            </div>
        </div>
    </header>

    <section class="hero dashboard-hero">
        <div class="container dashboard-hero-grid">
            <div class="hero-copy-card">
                <div class="eyebrow">Dashboard người dùng</div>
                <h1>Dashboard giá của tôi</h1>
                <p>Theo dõi sản phẩm đã lưu, xem trạng thái chạm giá và nhận gợi ý mua/chờ dựa trên lịch sử biến động giá.</p>
                <div class="hero-actions">
                    <a class="hero-action" href="index.php?role=user&controller=product&action=search">
                        <i class="fas fa-magnifying-glass"></i>Tìm thêm sản phẩm
                    </a>
                    <a class="hero-action secondary" href="index.php">
                        <i class="fas fa-house"></i>Về trang chủ
                    </a>
                </div>
            </div>

            <div class="hero-status-card">
                <div>
                    <div class="status-kicker">Trạng thái hôm nay</div>
                    <span class="status-main"><?php echo e_alerts($actionSummary); ?></span>
                    <p class="status-desc">
                        <?php echo $totalAlerts > 0 ? $readyPercent . '% danh sách theo dõi đã đạt điều kiện giá.' : 'Hãy thêm sản phẩm để hệ thống bắt đầu theo dõi và phân tích.'; ?>
                    </p>
                </div>
                <div>
                    <div class="progress-track" aria-label="Tỷ lệ đạt giá">
                        <div class="progress-fill" style="width: <?php echo max(0, min(100, $readyPercent)); ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="summary-panel dashboard-metrics" aria-label="Thống kê cảnh báo giá">
                <div class="summary-item">
                    <span class="summary-icon"><i class="fas fa-bookmark"></i></span>
                    <span class="summary-number"><?php echo number_format($totalAlerts); ?></span>
                    <span class="summary-label">Đang theo dõi</span>
                </div>
                <div class="summary-item">
                    <span class="summary-icon"><i class="fas fa-circle-check"></i></span>
                    <span class="summary-number"><?php echo number_format($readyAlerts); ?></span>
                    <span class="summary-label">Đã chạm giá</span>
                </div>
                <div class="summary-item">
                    <span class="summary-icon"><i class="fas fa-clock"></i></span>
                    <span class="summary-number"><?php echo number_format($waitingAlerts); ?></span>
                    <span class="summary-label">Đang canh giá</span>
                </div>
                <div class="summary-item">
                    <span class="summary-icon"><i class="fas fa-lightbulb"></i></span>
                    <span class="summary-number"><?php echo number_format($buyAdviceAlerts); ?></span>
                    <span class="summary-label">Nên mua</span>
                </div>
            </div>
        </div>
    </section>

    <main class="page-shell flex-grow-1">
        <div class="container">
            <?php if (($_GET['msg'] ?? '') === 'alert_removed'): ?>
                <div class="notice"><i class="fas fa-check-circle me-2"></i>Đã hủy theo dõi sản phẩm.</div>
            <?php endif; ?>

            <div class="notice d-none" data-alerts-message></div>

            <?php if (!empty($alerts)): ?>
                <div class="dashboard-section-head">
                    <div>
                        <h2>Gợi ý hành động theo giá</h2>
                        <p>Ưu tiên các sản phẩm đang có tín hiệu giá tốt hoặc nên chờ thêm trước khi mua.</p>
                    </div>
                    <a class="pill-link primary" href="index.php?role=user&controller=product&action=search&sort_by=price_asc">
                        <i class="fas fa-tags"></i>Tìm deal mới
                    </a>
                </div>

                <section class="decision-board" aria-label="Gợi ý hành động theo giá">
                    <div class="decision-panel buy">
                        <div class="decision-head">
                            <h2><i class="fas fa-circle-check me-2"></i>Nên mua</h2>
                            <span class="decision-count"><?php echo number_format($buyAdviceAlerts); ?></span>
                        </div>
                        <div class="decision-list">
                            <?php if (!empty($buyAdviceItems)): ?>
                                <?php foreach (array_slice($buyAdviceItems, 0, 3) as $decisionItem): ?>
                                    <?php echo dashboard_action_item($decisionItem); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="decision-empty">Chưa có sản phẩm nào đang ở vùng giá mua tốt.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="decision-panel wait">
                        <div class="decision-head">
                            <h2><i class="fas fa-clock me-2"></i>Nên chờ</h2>
                            <span class="decision-count"><?php echo number_format($waitAdviceAlerts); ?></span>
                        </div>
                        <div class="decision-list">
                            <?php if (!empty($waitAdviceItems)): ?>
                                <?php foreach (array_slice($waitAdviceItems, 0, 3) as $decisionItem): ?>
                                    <?php echo dashboard_action_item($decisionItem); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="decision-empty">Chưa có sản phẩm nào được khuyến nghị chờ thêm.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                </section>
            <?php endif; ?>

            <div class="toolbar dashboard-toolbar">
                <div>
                    <h2>Danh sách sản phẩm theo dõi</h2>
                    <p><?php echo $totalAlerts > 0 ? 'Quản lý các sản phẩm đã đặt cảnh báo giá, xem giá hiện tại và trạng thái phân tích.' : 'Bạn chưa có sản phẩm nào trong dashboard theo dõi giá.'; ?></p>
                </div>
            </div>

            <?php if (empty($alerts)): ?>
                <section class="empty-state">
                    <i class="fas fa-bell"></i>
                    <h3>Chưa có sản phẩm theo dõi</h3>
                    <p>Hãy tìm sản phẩm, mở trang chi tiết và đặt mức giá mong muốn. Danh sách này sẽ giúp bạn kiểm tra nhanh sản phẩm nào đã chạm giá.</p>
                    <a class="btn-main px-4" href="index.php"><i class="fas fa-bolt"></i> Khám phá deal</a>
                </section>
            <?php else: ?>
                <div class="alert-grid" data-alert-grid>
                    <?php foreach ($alerts as $item):
                        $productId = (int) ($item['product_id'] ?? 0);
                        $productName = $item['product_name'] ?? 'Sản phẩm';
                        $minPrice = (float) ($item['min_price'] ?? 0);
                        $targetPrice = (float) ($item['target_price'] ?? 0);
                        $isReached = ($minPrice > 0 && $targetPrice > 0 && $minPrice <= $targetPrice);
                        $createdAt = !empty($item['alert_created_at']) ? date('d/m/Y', strtotime($item['alert_created_at'])) : 'Chưa rõ ngày';
                        $analysis = $item['price_analysis'] ?? [];
                        $advice = $analysis['recommendation'] ?? ['code' => 'muted', 'label' => 'Đang theo dõi giá', 'message' => 'Các lần quét mới sẽ tự động cập nhật nhận định giá.'];
                        $trend = $analysis['trend'] ?? ['label' => 'Đang cập nhật'];
                        if (($advice['code'] ?? '') === 'insufficient') {
                            $advice = [
                                'code' => 'muted',
                                'label' => 'Đang theo dõi giá',
                                'message' => 'Các lần quét mới sẽ tự động cập nhật nhận định giá.',
                            ];
                            $trend = ['label' => 'Đang cập nhật'];
                        }
                    ?>
                        <article class="watch-card" data-alert-card data-product-id="<?php echo $productId; ?>">
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
                                        <span class="price-label">Giá tốt nhất hiện tại</span>
                                        <span class="current-price <?php echo $isReached ? 'ready' : ''; ?>"><?php echo money_alerts($minPrice); ?></span>
                                    </div>
                                </div>

                                <div class="watch-meta">
                                    <i class="fas fa-calendar-check me-1"></i> Theo dõi từ <?php echo e_alerts($createdAt); ?>
                                </div>

                                <div class="advice-box <?php echo e_alerts(advice_badge_class($advice['code'] ?? 'insufficient')); ?>">
                                    <div class="advice-title">
                                        <i class="fas fa-lightbulb"></i>
                                        <?php echo e_alerts($advice['label'] ?? 'Đang theo dõi giá'); ?>
                                    </div>
                                    <div class="advice-text">
                                        <?php echo e_alerts($advice['message'] ?? 'Các lần quét mới sẽ tự động cập nhật nhận định giá.'); ?>
                                    </div>
                                    <div class="advice-text mt-1">
                                        Xu hướng: <?php echo e_alerts($trend['label'] ?? 'Đang cập nhật'); ?>
                                    </div>
                                </div>

                                <div class="watch-actions">
                                    <a class="btn-main" href="index.php?role=user&controller=product&action=detail&id=<?php echo $productId; ?>">
                                        <i class="fas fa-chart-line"></i> Chi tiết giá
                                    </a>
                                    <a class="btn-icon" href="index.php?role=user&controller=product&action=removeAlert&id=<?php echo $productId; ?>&redirect=my_alerts"
                                       data-remove-alert
                                       data-product-id="<?php echo $productId; ?>"
                                       data-confirm-message="Bạn muốn hủy theo dõi sản phẩm này?"
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

    <?php require __DIR__ . '/partials/footer.php'; ?>

    <script>
        const alertsGrid = document.querySelector('[data-alert-grid]');
        const alertsMessage = document.querySelector('[data-alerts-message]');

        function showAlertsMessage(message, type = 'success') {
            if (!alertsMessage) return;
            alertsMessage.textContent = message || '';
            alertsMessage.classList.remove('d-none');
            alertsMessage.style.borderColor = type === 'success' ? '#bbf7d0' : '#fecdd3';
            alertsMessage.style.background = type === 'success' ? '#f0fdf4' : '#fff1f2';
            alertsMessage.style.color = type === 'success' ? '#166534' : '#9f1239';
        }

        async function readAlertsJson(response) {
            try {
                return await response.json();
            } catch (error) {
                return { success: false, message: 'Máy chủ trả về dữ liệu không hợp lệ.' };
            }
        }

        document.querySelectorAll('[data-remove-alert]').forEach((link) => {
            link.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopImmediatePropagation();

                const confirmMessage = link.dataset.confirmMessage || 'Bạn muốn hủy theo dõi sản phẩm này?';
                if (!window.confirm(confirmMessage)) return;

                link.style.pointerEvents = 'none';
                link.style.opacity = '.6';

                try {
                    const formData = new FormData();
                    formData.append('product_id', link.dataset.productId || '');
                    formData.append('redirect', 'my_alerts');

                    const response = await fetch(link.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await readAlertsJson(response);

                    if (!response.ok || !data.success) {
                        showAlertsMessage(data.message || 'Không thể hủy theo dõi sản phẩm.', 'danger');
                        link.style.pointerEvents = '';
                        link.style.opacity = '';
                        return;
                    }

                    const card = link.closest('[data-alert-card]');
                    if (card) card.remove();
                    showAlertsMessage(data.message || 'Đã hủy theo dõi sản phẩm.', 'success');

                    if (alertsGrid && !alertsGrid.querySelector('[data-alert-card]')) {
                        window.setTimeout(() => window.location.reload(), 500);
                    }
                } catch (error) {
                    showAlertsMessage('Không thể kết nối máy chủ. Vui lòng thử lại.', 'danger');
                    link.style.pointerEvents = '';
                    link.style.opacity = '';
                }
            }, true);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
