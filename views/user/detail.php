<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e_detail($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money_detail($value) {
    return (!empty($value) && (int) $value > 0) ? number_format((int) $value) . ' đ' : 'Đang cập nhật';
}

function platform_logo($platform) {
    return [
        'Tiki' => 'https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png',
        'Shopee' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg',
        'Lazada' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png',
    ][$platform] ?? '';
}

$cheapest = !empty($platforms) ? (int) ($platforms[0]['current_price'] ?? 0) : 0;
$avgPrice = !empty($priceStats['avg_price']) ? (int) round($priceStats['avg_price']) : 0;
$diffAvg = ($avgPrice > 0 && $cheapest > 0) ? (($avgPrice - $cheapest) / $avgPrice) * 100 : 0;
$thumbnail = trim((string) ($product['thumbnail_url'] ?? ''));
$productSpecs = $productSpecs ?? [];
$priceAnalysis = $priceAnalysis ?? [];
$recommendation = $priceAnalysis['recommendation'] ?? ['code' => 'insufficient', 'label' => 'Chưa đủ dữ liệu', 'message' => '', 'reason' => ''];
$trend = $priceAnalysis['trend'] ?? ['code' => 'insufficient', 'label' => 'Chưa đủ dữ liệu', 'message' => '', 'change_percent' => 0];
$confidence = $priceAnalysis['confidence'] ?? ['code' => 'none', 'label' => 'Chưa có dữ liệu', 'message' => ''];

function analysis_tone($code) {
    return [
        'buy' => 'good',
        'consider' => 'neutral',
        'wait' => 'warn',
        'decreasing' => 'good',
        'stable' => 'neutral',
        'increasing' => 'warn',
        'good' => 'good',
        'medium' => 'neutral',
        'low' => 'warn',
    ][$code] ?? 'muted';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e_detail($product['name']); ?> - SmartPrice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --ink:#111827; --muted:#667085; --line:#e6e8ef; --page:#f5f7fb; --brand:#f7c600; --accent:#d92d20; --blue:#0b5fff; --green:#0f8a5f; }
        body { background:var(--page); color:var(--ink); font-family:"Segoe UI",Arial,sans-serif; }
        a { color:inherit; }
        .header { background:#111827; box-shadow:0 8px 20px rgba(16,24,40,0.18); position:sticky; top:0; z-index:1020; }
        .header-inner { min-height:72px; display:flex; align-items:center; justify-content:space-between; gap:18px; }
        .brand { color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:10px; font-weight:900; font-size:1.3rem; }
        .brand-icon { width:40px; height:40px; border-radius:8px; display:grid; place-items:center; background:var(--brand); color:#111827; }
        .header-actions { display:flex; gap:10px; align-items:center; }
        .header-btn { min-height:42px; display:inline-flex; align-items:center; gap:8px; padding:0 14px; color:#fff; text-decoration:none; border:1px solid rgba(255,255,255,0.18); background:rgba(255,255,255,0.08); border-radius:8px; font-weight:800; }
        .header-btn:hover { color:#fff; background:rgba(255,255,255,0.14); }
        .page { padding:24px 0 48px; }
        .product-hero { display:grid; grid-template-columns:420px minmax(0,1fr); gap:18px; margin-bottom:18px; }
        .panel { background:#fff; border:1px solid var(--line); border-radius:8px; }
        .gallery { padding:18px; }
        .product-image { height:390px; border-radius:8px; background:#f8fafc; display:grid; place-items:center; overflow:hidden; }
        .product-image img { width:100%; height:100%; object-fit:contain; padding:18px; }
        .product-image i { font-size:5rem; color:#cbd5e1; }
        .summary { padding:22px; display:flex; flex-direction:column; min-height:426px; }
        .tag-row { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
        .tag { height:28px; padding:0 10px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; font-size:.78rem; font-weight:900; }
        .tag.hot { background:#fff1f0; color:var(--accent); }
        .tag.good { background:#ecfdf3; color:var(--green); }
        .product-title { font-size:1.72rem; line-height:1.25; font-weight:900; margin:0 0 10px; }
        .description { color:var(--muted); font-weight:600; margin-bottom:18px; }
        .metric-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-top:auto; }
        .metric { border:1px solid var(--line); border-radius:8px; padding:14px; background:#fff; }
        .metric span { display:block; color:var(--muted); font-size:.78rem; font-weight:900; text-transform:uppercase; margin-bottom:4px; }
        .metric strong { color:var(--accent); font-size:1.28rem; font-weight:900; }
        .content-layout { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:18px; }
        .section-card { background:#fff; border:1px solid var(--line); border-radius:8px; padding:18px; margin-bottom:18px; }
        .section-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; }
        .section-title { margin:0; font-size:1.16rem; font-weight:900; }
        .spec-layout { display:grid; gap:14px; }
        .spec-group { border:1px solid var(--line); border-radius:8px; overflow:hidden; background:#fff; }
        .spec-group-title { margin:0; padding:12px 14px; background:#f8fafc; border-bottom:1px solid var(--line); font-size:.98rem; font-weight:900; }
        .spec-table { display:grid; }
        .spec-row { display:grid; grid-template-columns:220px minmax(0,1fr); gap:14px; padding:12px 14px; border-bottom:1px solid var(--line); }
        .spec-row:last-child { border-bottom:0; }
        .spec-row:nth-child(even) { background:#fcfcfd; }
        .spec-name { color:var(--muted); font-weight:800; }
        .spec-value { font-weight:700; overflow-wrap:anywhere; }
        .seller-row { display:grid; grid-template-columns:140px minmax(0,1fr) auto; align-items:center; gap:16px; padding:14px; border:1px solid var(--line); border-radius:8px; margin-bottom:10px; }
        .seller-row.best { border-color:#fecdca; background:#fff8f7; }
        .seller-logo { width:92px; height:34px; object-fit:contain; }
        .seller-price { color:var(--accent); font-size:1.22rem; font-weight:900; }
        .seller-note { color:var(--muted); font-size:.82rem; font-weight:700; }
        .buy-btn { min-height:42px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:8px; padding:0 14px; background:#111827; color:#fff; text-decoration:none; font-weight:900; white-space:nowrap; }
        .buy-btn:hover { color:#fff; background:#1f2937; }
        .buy-btn.best { background:var(--accent); }
        .alert-card { position:sticky; top:96px; padding:18px; }
        .alert-icon { width:46px; height:46px; border-radius:8px; display:grid; place-items:center; background:#fffbeb; color:#111827; margin-bottom:12px; }
        .form-control { height:48px; border-radius:8px; border:1px solid var(--line); font-weight:800; }
        .form-control:focus { border-color:var(--brand); box-shadow:0 0 0 4px rgba(247,198,0,.18); }
        .preset-row { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
        .preset-row button { height:38px; border:1px solid var(--line); border-radius:8px; background:#fff; color:var(--accent); font-weight:900; }
        .primary-btn { height:48px; border:0; border-radius:8px; background:#111827; color:#fff; font-weight:900; width:100%; }
        .primary-btn:hover { background:#1f2937; color:#fff; }
        .secondary-link { height:44px; display:flex; align-items:center; justify-content:center; border-radius:8px; text-decoration:none; background:#fff5f5; color:var(--accent); border:1px solid #fee4e2; font-weight:900; }
        .chart-wrap {
            position: relative;
            height: 390px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px 14px 8px;
            background:
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .chart-wrap canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-empty {
            position: absolute;
            inset: 18px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: rgba(248,250,252,.92);
            color: var(--muted);
            text-align: center;
            font-weight: 800;
            z-index: 3;
        }

        .chart-empty i {
            display: block;
            margin-bottom: 10px;
            color: #94a3b8;
            font-size: 2rem;
        }

        .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
        .stat-pill { border:1px solid var(--line); border-radius:8px; padding:14px; background:#fff; }
        .stat-pill span { color:var(--muted); font-size:.78rem; font-weight:900; display:block; }
        .stat-pill strong { font-size:1.12rem; font-weight:950; }
        .analysis-layout { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .analysis-callout { border:1px solid var(--line); border-radius:8px; padding:16px; background:#f8fafc; }
        .analysis-callout.good { border-color:#bbf7d0; background:#f0fdf4; }
        .analysis-callout.warn { border-color:#fed7aa; background:#fff7ed; }
        .analysis-callout.neutral { border-color:#bfdbfe; background:#eff6ff; }
        .analysis-callout.muted { border-color:var(--line); background:#f8fafc; }
        .analysis-label { display:inline-flex; align-items:center; gap:7px; min-height:28px; padding:0 10px; border-radius:999px; font-size:.78rem; font-weight:900; background:#fff; border:1px solid rgba(16,24,40,.08); margin-bottom:10px; }
        .analysis-callout.good .analysis-label { color:#166534; }
        .analysis-callout.warn .analysis-label { color:#9a3412; }
        .analysis-callout.neutral .analysis-label { color:#1d4ed8; }
        .analysis-callout h3 { margin:0 0 8px; font-size:1.05rem; font-weight:950; }
        .analysis-callout p { margin:0; color:var(--muted); font-weight:700; }
        .analysis-metrics { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-top:14px; }
        .analysis-metric { border:1px solid var(--line); border-radius:8px; padding:12px; background:#fff; }
        .analysis-metric span { display:block; color:var(--muted); font-size:.76rem; font-weight:900; margin-bottom:4px; }
        .analysis-metric strong { font-size:1rem; font-weight:950; }
        .related-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
        .related-card { background:#fff; border:1px solid var(--line); border-radius:8px; padding:12px; text-decoration:none; display:block; height:100%; }
        .related-img { height:135px; border-radius:8px; background:#f8fafc; display:grid; place-items:center; overflow:hidden; margin-bottom:10px; }
        .related-img img { width:100%; height:100%; object-fit:contain; padding:8px; }
        .related-name { min-height:40px; font-weight:800; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        @media (max-width:1100px){ .product-hero,.content-layout{grid-template-columns:1fr;} .alert-card{position:static;} .related-grid{grid-template-columns:repeat(2,1fr);} }
        @media (max-width:820px){ .analysis-layout,.analysis-metrics{grid-template-columns:1fr 1fr;} }
        @media (max-width:640px){ .seller-row,.spec-row{grid-template-columns:1fr;} .metric-grid,.stats-row,.analysis-layout,.analysis-metrics{grid-template-columns:1fr;} .related-grid{grid-template-columns:1fr;} .header-inner{flex-direction:column; padding:14px 0;} }
    </style>
</head>
<body>
<header class="header">
    <div class="container header-inner">
        <a href="index.php" class="brand"><span class="brand-icon"><i class="fas fa-tags"></i></span>SmartPrice</a>
        <div class="header-actions">
            <a href="javascript:history.back()" class="header-btn"><i class="fas fa-arrow-left"></i>Quay lại</a>
            <a href="index.php" class="header-btn"><i class="fas fa-home"></i>Trang chủ</a>
        </div>
    </div>
</header>

<main class="container page">
    <section class="product-hero">
        <div class="panel gallery">
            <div class="product-image">
                <?php if ($thumbnail !== ''): ?>
                    <img src="<?php echo e_detail($thumbnail); ?>" alt="<?php echo e_detail($product['name']); ?>">
                <?php else: ?>
                    <i class="fas fa-box-open"></i>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel summary">
            <div class="tag-row">
                <span class="tag hot"><i class="fas fa-fire"></i>So sánh đa sàn</span>
                <?php if($diffAvg > 5): ?>
                    <span class="tag good"><i class="fas fa-arrow-down"></i>Thấp hơn trung bình <?php echo (int) round($diffAvg); ?>%</span>
                <?php endif; ?>
            </div>
            <h1 class="product-title"><?php echo e_detail($product['name']); ?></h1>
            <p class="description"><?php echo e_detail($product['description'] ?? ''); ?></p>
            <div class="metric-grid">
                <div class="metric"><span>Giá tốt nhất</span><strong><?php echo money_detail($cheapest); ?></strong></div>
                <div class="metric"><span>Số sàn</span><strong><?php echo count($platforms); ?></strong></div>
                <div class="metric"><span>Trung bình</span><strong><?php echo money_detail($avgPrice); ?></strong></div>
            </div>
        </div>
    </section>

    <section class="content-layout">
        <div>
            <div class="section-card">
                <div class="section-head">
                    <h2 class="section-title"><i class="fas fa-store text-primary me-2"></i>So sánh nơi bán</h2>
                    <span class="text-muted small fw-bold">Sắp xếp theo giá thấp nhất</span>
                </div>
                <?php if(empty($platforms)): ?>
                    <div class="text-center text-muted py-4 fw-bold">Chưa có link sàn đang hoạt động.</div>
                <?php else: ?>
                    <?php foreach ($platforms as $index => $p): ?>
                        <?php
                        $price = (int) ($p['current_price'] ?? 0);
                        $isBest = $index === 0 && $price > 0;
                        $diffFromMin = ($price > 0 && $cheapest > 0) ? $price - $cheapest : 0;
                        ?>
                        <div class="seller-row <?php echo $isBest ? 'best' : ''; ?>">
                            <img class="seller-logo" src="<?php echo e_detail(platform_logo($p['platform_name'])); ?>" alt="<?php echo e_detail($p['platform_name']); ?>">
                            <div>
                                <div class="seller-price"><?php echo money_detail($price); ?></div>
                                <div class="seller-note">
                                    <?php if($isBest): ?>
                                        Rẻ nhất hiện tại
                                    <?php elseif($diffFromMin > 0): ?>
                                        Cao hơn <?php echo money_detail($diffFromMin); ?>
                                    <?php else: ?>
                                        Đang cập nhật trạng thái giá
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?php echo e_detail($p['product_url']); ?>" target="_blank" class="buy-btn <?php echo $isBest ? 'best' : ''; ?>">
                                Tới nơi bán <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="section-card">
                <div class="section-head">
                    <h2 class="section-title"><i class="fas fa-chart-simple text-primary me-2"></i>Phân tích giá & gợi ý mua</h2>
                    <span class="text-muted small fw-bold">Dựa trên <?php echo e_detail($priceAnalysis['period_label'] ?? '30 ngày gần nhất'); ?></span>
                </div>

                <div class="analysis-layout">
                    <div class="analysis-callout <?php echo e_detail(analysis_tone($recommendation['code'] ?? 'insufficient')); ?>">
                        <div class="analysis-label">
                            <i class="fas fa-lightbulb"></i><?php echo e_detail($recommendation['label'] ?? 'Chưa đủ dữ liệu'); ?>
                        </div>
                        <h3>Gợi ý thời điểm mua</h3>
                        <p><?php echo e_detail($recommendation['message'] ?? 'Hệ thống cần thêm dữ liệu lịch sử trước khi gợi ý.'); ?></p>
                        <?php if (!empty($recommendation['reason'])): ?>
                            <p class="mt-2 small"><?php echo e_detail($recommendation['reason']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="analysis-callout <?php echo e_detail(analysis_tone($trend['code'] ?? 'insufficient')); ?>">
                        <div class="analysis-label">
                            <i class="fas fa-arrow-trend-up"></i><?php echo e_detail($trend['label'] ?? 'Chưa đủ dữ liệu'); ?>
                        </div>
                        <h3>Xu hướng giá cơ bản</h3>
                        <p><?php echo e_detail($trend['message'] ?? 'Cần thêm lịch sử giá để xác định xu hướng.'); ?></p>
                        <?php if (($trend['code'] ?? '') !== 'insufficient'): ?>
                            <p class="mt-2 small">Mức thay đổi trong kỳ: <?php echo e_detail(($trend['change_percent'] ?? 0) . '%'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="analysis-metrics">
                    <div class="analysis-metric">
                        <span>Thấp nhất kỳ này</span>
                        <strong class="text-danger"><?php echo money_detail($priceAnalysis['min_price'] ?? 0); ?></strong>
                    </div>
                    <div class="analysis-metric">
                        <span>Cao nhất kỳ này</span>
                        <strong><?php echo money_detail($priceAnalysis['max_price'] ?? 0); ?></strong>
                    </div>
                    <div class="analysis-metric">
                        <span>So với trung bình</span>
                        <strong><?php echo ((int) ($priceAnalysis['data_points'] ?? 0) >= 3) ? e_detail(($priceAnalysis['current_vs_avg_percent'] ?? 0) . '%') : 'Chưa đủ'; ?></strong>
                    </div>
                    <div class="analysis-metric">
                        <span>Biên độ dao động</span>
                        <strong><?php echo ((int) ($priceAnalysis['data_points'] ?? 0) >= 3) ? e_detail(($priceAnalysis['volatility_percent'] ?? 0) . '%') : 'Chưa đủ'; ?></strong>
                    </div>
                    <div class="analysis-metric">
                        <span>Độ tin cậy</span>
                        <strong class="<?php echo e_detail(($confidence['code'] ?? '') === 'good' ? 'text-success' : ((($confidence['code'] ?? '') === 'low') ? 'text-warning' : 'text-primary')); ?>">
                            <?php echo e_detail($confidence['label'] ?? 'Chưa có dữ liệu'); ?>
                        </strong>
                    </div>
                    <div class="analysis-metric">
                        <span>Lần tăng giá</span>
                        <strong><?php echo (int) ($priceAnalysis['up_count'] ?? 0); ?></strong>
                    </div>
                    <div class="analysis-metric">
                        <span>Lần giảm giá</span>
                        <strong><?php echo (int) ($priceAnalysis['down_count'] ?? 0); ?></strong>
                    </div>
                    <div class="analysis-metric">
                        <span>Sàn tốt nhất</span>
                        <strong><?php echo e_detail($priceAnalysis['best_platform'] ?? 'Chưa có'); ?></strong>
                    </div>
                </div>
            </div>

            <?php if(!empty($productSpecs)): ?>
                <div class="section-card">
                    <div class="section-head">
                        <h2 class="section-title"><i class="fas fa-list-check text-primary me-2"></i>Thông tin chi tiết sản phẩm</h2>
                        <span class="text-muted small fw-bold">Tự động lấy từ sàn</span>
                    </div>
                    <div class="spec-layout">
                        <?php foreach($productSpecs as $groupName => $items): ?>
                            <div class="spec-group">
                                <h3 class="spec-group-title"><?php echo e_detail($groupName); ?></h3>
                                <div class="spec-table">
                                    <?php foreach($items as $spec): ?>
                                        <div class="spec-row">
                                            <div class="spec-name"><?php echo e_detail($spec['spec_name'] ?? ''); ?></div>
                                            <div class="spec-value"><?php echo e_detail($spec['spec_value'] ?? ''); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="section-card">
                <div class="section-head">
                    <h2 class="section-title"><i class="fas fa-chart-line text-success me-2"></i>Lịch sử giá</h2>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="updateChartData(7, this)">7 ngày</button>
                        <button type="button" class="btn btn-outline-secondary active" onclick="updateChartData(30, this)">30 ngày</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="updateChartData(null, this)">Tất cả</button>
                    </div>
                </div>
                <div class="stats-row">
                    <div class="stat-pill"><span>Cao nhất</span><strong id="chartMaxPrice"><?php echo money_detail($priceStats['max_price'] ?? 0); ?></strong></div>
                    <div class="stat-pill"><span>Thấp nhất</span><strong id="chartMinPrice" class="text-danger"><?php echo money_detail($priceStats['min_price'] ?? 0); ?></strong></div>
                    <div class="stat-pill"><span>Trung bình</span><strong id="chartAvgPrice" class="text-primary"><?php echo money_detail($priceStats['avg_price'] ?? 0); ?></strong></div>
                </div>
                <div class="chart-wrap">
                    <canvas id="priceHistoryChart"></canvas>
                    <div id="priceChartEmpty" class="chart-empty d-none">
                        <div>
                            <i class="fas fa-chart-line"></i>
                            Chưa đủ dữ liệu lịch sử giá trong khoảng đã chọn.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <aside>
            <div class="panel alert-card">
                <div class="alert-icon"><i class="fas fa-bell"></i></div>
                <h2 class="section-title mb-2">Theo dõi giá</h2>
                <p class="text-muted fw-semibold small">Nhận email khi sản phẩm giảm xuống mức giá bạn đặt.</p>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="alert py-2 small fw-bold d-none" data-alert-message></div>
                    <form action="index.php?role=user&controller=product&action=setAlert" method="POST" data-alert-form>
                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                        <label class="form-label fw-bold">Giá mong muốn</label>
                        <div class="input-group mb-3">
                            <input type="text" id="targetPriceInput" name="target_price" class="form-control" value="<?php echo isset($userAlert) && $userAlert ? number_format((int) $userAlert['target_price'], 0, ',', '.') : number_format(max($cheapest, 0), 0, ',', '.'); ?>" required>
                            <span class="input-group-text fw-bold">đ</span>
                        </div>
                        <div class="preset-row mb-3">
                            <button type="button" onclick="setPresetPrice(0.95)">-5%</button>
                            <button type="button" onclick="setPresetPrice(0.90)">-10%</button>
                            <button type="button" onclick="setPresetPrice(0.80)">-20%</button>
                        </div>
                        <div class="alert alert-success py-2 small fw-bold" id="savingInsight">
                            Tiết kiệm dự kiến: <span id="savingAmount">0 đ</span>
                        </div>
                        <button type="submit" class="primary-btn"><?php echo isset($userAlert) && $userAlert ? 'Cập nhật mức giá' : 'Bật theo dõi giá'; ?></button>
                        <?php if(isset($userAlert) && $userAlert): ?>
                            <a class="secondary-link mt-2" href="index.php?role=user&controller=product&action=removeAlert&id=<?php echo (int) $product['id']; ?>">Hủy theo dõi</a>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <a href="index.php?role=user&controller=auth&action=login" class="primary-btn text-decoration-none d-flex align-items-center justify-content-center">Đăng nhập để theo dõi</a>
                <?php endif; ?>
            </div>
        </aside>
    </section>

    <?php if(!empty($relatedProducts)): ?>
        <section class="section-card">
            <div class="section-head">
                <h2 class="section-title"><i class="fas fa-layer-group text-primary me-2"></i>Sản phẩm tương tự</h2>
            </div>
            <div class="related-grid">
                <?php foreach($relatedProducts as $rp): ?>
                    <a href="index.php?role=user&controller=product&action=detail&id=<?php echo (int) $rp['id']; ?>" class="related-card">
                        <div class="related-img">
                            <?php if(!empty($rp['thumbnail_url'])): ?>
                                <img src="<?php echo e_detail($rp['thumbnail_url']); ?>" alt="<?php echo e_detail($rp['name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-box-open text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div class="related-name"><?php echo e_detail($rp['name']); ?></div>
                        <div class="text-danger fw-bold mt-2"><?php echo money_detail($rp['min_price'] ?? 0); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script>
const rawData = <?php echo json_encode($priceHistory, JSON_UNESCAPED_UNICODE); ?> || [];
const dailyData = {};
rawData.sort((a, b) => new Date(a.scraped_at) - new Date(b.scraped_at));

rawData.forEach(item => {
    const dateOnly = String(item.scraped_at).split(' ')[0];
    if (!dailyData[dateOnly]) dailyData[dateOnly] = { Tiki: null, Shopee: null, Lazada: null };
    const price = Number(item.price || 0);
    if (price > 0) {
        dailyData[dateOnly][item.platform_name] = price;
    }
});

const rawLabels = Object.keys(dailyData);
let priceChart;

const platformStyles = {
    Tiki: { color: '#189eff', soft: 'rgba(24,158,255,.08)' },
    Shopee: { color: '#ee4d2d', soft: 'rgba(238,77,45,.08)' },
    Lazada: { color: '#152b91', soft: 'rgba(21,43,145,.08)' }
};

function parseDateKey(dateKey) {
    const parts = String(dateKey).split('-').map(Number);
    return new Date(parts[0], parts[1] - 1, parts[2]);
}

function toDateKey(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateLabel(dateKey) {
    const parts = String(dateKey).split('-');
    return `${parts[2]}/${parts[1]}`;
}

function formatChartMoney(value) {
    return Number(value || 0).toLocaleString('vi-VN') + ' đ';
}

function getVisibleDateKeys(days = null) {
    if (!days || rawLabels.length === 0) {
        if (rawLabels.length !== 1) return rawLabels;

        const nextDate = parseDateKey(rawLabels[0]);
        nextDate.setDate(nextDate.getDate() + 1);
        return [rawLabels[0], toDateKey(nextDate)];
    }

    const latestDate = parseDateKey(rawLabels[rawLabels.length - 1]);
    const fromDate = new Date(latestDate);
    fromDate.setDate(latestDate.getDate() - Number(days) + 1);

    const visibleKeys = rawLabels.filter(dateKey => parseDateKey(dateKey) >= fromDate);
    const boundaryKeys = [toDateKey(fromDate), ...visibleKeys, toDateKey(latestDate)];

    return [...new Set(boundaryKeys)].sort((a, b) => parseDateKey(a) - parseDateKey(b));
}

function buildAlignedSeries(dateKeys, platform) {
    const actualValues = dateKeys.map(dateKey => dailyData[dateKey]?.[platform] ?? null);
    const validIndexes = actualValues
        .map((value, index) => Number(value) > 0 ? index : -1)
        .filter(index => index >= 0);

    if (!validIndexes.length) {
        return {
            data: dateKeys.map(() => null),
            actualData: [],
            actualDateKeys: []
        };
    }

    const firstValue = actualValues[validIndexes[0]];
    let lastKnown = firstValue;
    const alignedData = actualValues.map(value => {
        if (Number(value) > 0) {
            lastKnown = value;
            return value;
        }
        return lastKnown;
    });

    return {
        data: alignedData,
        actualData: actualValues.filter(value => Number(value) > 0),
        actualDateKeys: validIndexes.map(index => dateKeys[index])
    };
}

function visiblePricesFromDatasets(datasets) {
    return datasets.flatMap(dataset => dataset._actualData || []).filter(value => Number(value) > 0);
}

function updateChartStats(prices) {
    const maxEl = document.getElementById('chartMaxPrice');
    const minEl = document.getElementById('chartMinPrice');
    const avgEl = document.getElementById('chartAvgPrice');

    if (!maxEl || !minEl || !avgEl) return;

    if (!prices.length) {
        maxEl.textContent = 'Đang cập nhật';
        minEl.textContent = 'Đang cập nhật';
        avgEl.textContent = 'Đang cập nhật';
        return;
    }

    const maxPrice = Math.max(...prices);
    const minPrice = Math.min(...prices);
    const avgPrice = Math.round(prices.reduce((sum, value) => sum + value, 0) / prices.length);

    maxEl.textContent = formatChartMoney(maxPrice);
    minEl.textContent = formatChartMoney(minPrice);
    avgEl.textContent = formatChartMoney(avgPrice);
}

function initChart(days = null) {
    const ctx = document.getElementById('priceHistoryChart');
    const emptyState = document.getElementById('priceChartEmpty');
    if (!ctx) return;
    if (priceChart) priceChart.destroy();

    const dateKeys = getVisibleDateKeys(days);
    const labels = dateKeys.map(formatDateLabel);
    const datasets = Object.keys(platformStyles).map(platform => {
        const series = buildAlignedSeries(dateKeys, platform);
        return {
            label: platform,
            data: series.data,
            _actualData: series.actualData,
            _dateKeys: dateKeys,
            _actualDateKeys: new Set(series.actualDateKeys),
            borderColor: platformStyles[platform].color,
            backgroundColor: platformStyles[platform].soft,
            pointBackgroundColor: '#fff',
            pointBorderColor: platformStyles[platform].color,
            pointBorderWidth: 2,
            pointRadius: context => {
                const dateKey = context.dataset._dateKeys?.[context.dataIndex];
                return context.dataset._actualDateKeys?.has(dateKey) ? 4 : 0;
            },
            pointHoverRadius: 6,
            fill: false,
            tension: .28,
            borderWidth: 3,
            spanGaps: true
        };
    });
    const visiblePrices = visiblePricesFromDatasets(datasets);
    updateChartStats(visiblePrices);

    if (emptyState) {
        emptyState.classList.toggle('d-none', visiblePrices.length > 0);
    }

    const minPrice = visiblePrices.length ? Math.min(...visiblePrices) : 0;
    const maxPrice = visiblePrices.length ? Math.max(...visiblePrices) : 0;
    const padding = Math.max(100000, Math.round((maxPrice - minPrice) * 0.12));

    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'nearest', intersect: false },
            layout: { padding: { top: 8, right: 12, bottom: 0, left: 6 } },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 18,
                        color: '#344054',
                        font: { weight: '700' }
                    }
                },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: context => {
                            if (!context.parsed.y) return null;
                            const dateKey = context.dataset._dateKeys?.[context.dataIndex];
                            const suffix = context.dataset._actualDateKeys?.has(dateKey) ? '' : ' (giữ theo giá gần nhất)';
                            return ` ${context.dataset.label}: ${formatChartMoney(context.parsed.y)}${suffix}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#667085', maxRotation: 0, autoSkipPadding: 18 }
                },
                y: {
                    min: visiblePrices.length ? Math.max(0, minPrice - padding) : undefined,
                    max: visiblePrices.length ? maxPrice + padding : undefined,
                    border: { display: false },
                    grid: { color: '#edf2f7' },
                    ticks: {
                        color: '#667085',
                        padding: 8,
                        callback: value => formatChartMoney(value)
                    }
                }
            }
        }
    });
}

function updateChartData(days, button) {
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    if (button) button.classList.add('active');
    initChart(days);
}

initChart(30);

const cheapestPrice = <?php echo (int) $cheapest; ?>;
const priceInput = document.getElementById('targetPriceInput');
const savingText = document.getElementById('savingAmount');

function formatCurrency(num) {
    return parseInt(num || 0, 10).toLocaleString('vi-VN').replace(/,/g, '.');
}

function calculateSaving() {
    if (!priceInput || !savingText) return;
    const target = parseInt(priceInput.value.replace(/[^0-9]/g, ''), 10) || 0;
    const saved = cheapestPrice > target && target > 0 ? cheapestPrice - target : 0;
    savingText.textContent = formatCurrency(saved) + ' đ';
}

function setPresetPrice(multiplier) {
    if (!priceInput || cheapestPrice <= 0) return;
    priceInput.value = formatCurrency(Math.floor(cheapestPrice * multiplier));
    calculateSaving();
}

if (priceInput) {
    priceInput.addEventListener('input', function() {
        const val = this.value.replace(/[^0-9]/g, '');
        this.value = val ? formatCurrency(val) : '';
        calculateSaving();
    });
    calculateSaving();
}

const alertForm = document.querySelector('[data-alert-form]');
const alertMessage = document.querySelector('[data-alert-message]');

function showAlertFeedback(message, type = 'success') {
    if (!alertMessage) return;
    alertMessage.textContent = message || '';
    alertMessage.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
    alertMessage.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
}

async function readJsonResponse(response) {
    try {
        return await response.json();
    } catch (error) {
        return { success: false, message: 'Máy chủ trả về dữ liệu không hợp lệ.' };
    }
}

if (alertForm) {
    alertForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = alertForm.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(alertForm.action, {
                method: 'POST',
                body: new FormData(alertForm),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await readJsonResponse(response);

            if (!response.ok || !data.success) {
                showAlertFeedback(data.message || 'Không thể lưu mức giá theo dõi.', 'danger');
                return;
            }

            showAlertFeedback(data.message || 'Đã lưu mức giá theo dõi.', 'success');
            if (submitButton) submitButton.textContent = 'Cập nhật mức giá';

            if (!alertForm.querySelector('.secondary-link')) {
                const productId = data.product_id || alertForm.querySelector('[name="product_id"]')?.value || '';
                const removeLink = document.createElement('a');
                removeLink.className = 'secondary-link mt-2';
                removeLink.href = `index.php?role=user&controller=product&action=removeAlert&id=${encodeURIComponent(productId)}`;
                removeLink.textContent = 'Hủy theo dõi';
                alertForm.appendChild(removeLink);
            }
        } catch (error) {
            showAlertFeedback('Không thể kết nối máy chủ. Vui lòng thử lại.', 'danger');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });

    alertForm.addEventListener('click', async (event) => {
        const removeLink = event.target.closest('.secondary-link');
        if (!removeLink || !alertForm.contains(removeLink)) return;

        event.preventDefault();
        removeLink.classList.add('disabled');
        removeLink.style.pointerEvents = 'none';

        try {
            const productId = alertForm.querySelector('[name="product_id"]')?.value || '';
            const formData = new FormData();
            formData.append('product_id', productId);

            const response = await fetch(removeLink.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await readJsonResponse(response);

            if (!response.ok || !data.success) {
                showAlertFeedback(data.message || 'Không thể hủy theo dõi sản phẩm.', 'danger');
                removeLink.classList.remove('disabled');
                removeLink.style.pointerEvents = '';
                return;
            }

            removeLink.remove();
            const submitButton = alertForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.textContent = 'Bật theo dõi giá';
            showAlertFeedback(data.message || 'Đã hủy theo dõi sản phẩm.', 'success');
        } catch (error) {
            showAlertFeedback('Không thể kết nối máy chủ. Vui lòng thử lại.', 'danger');
            removeLink.classList.remove('disabled');
            removeLink.style.pointerEvents = '';
        }
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
