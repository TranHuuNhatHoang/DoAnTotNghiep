<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } 
// Logic tính toán Insight (Chỉ chạy ở View)
$cheapest = !empty($platforms) ? $platforms[0]['current_price'] : 0;
$avgPrice = !empty($priceStats['avg_price']) ? round($priceStats['avg_price']) : 0;
$diffAvg = $avgPrice > 0 ? (($avgPrice - $cheapest) / $avgPrice) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - So Sánh Giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f5f5fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .rounded-4 { border-radius: 1rem !important; }
        .shadow-hover:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; transform: translateY(-2px); transition: 0.3s; }
        
        /* Product Image Placeholder */
        .product-gallery { background: #fff; border: 1px solid #eee; height: 350px; display: flex; align-items: center; justify-content: center; }
        
        /* Table So sánh */
        .comparison-table tbody tr:hover { background-color: #f8f9fa; }
        .cheapest-row { border: 2px solid #ff424e !important; background-color: #fff4f4 !important; border-radius: 10px; }
        .plat-logo { height: 25px; width: 80px; object-fit: contain; }
        
        /* Custom Button & Slider */
        .btn-buy { background: #ff424e; color: white; font-weight: bold; }
        .btn-buy:hover { background: #e03a45; color: white; }
        input[type=range] { accent-color: #ffc107; }
        
        /* Skeleton Animation */
        @keyframes skeleton-loading { 0% { background-color: #e9ecef; } 50% { background-color: #dee2e6; } 100% { background-color: #e9ecef; } }
        .skeleton { animation: skeleton-loading 1.5s infinite; border-radius: 4px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-search-dollar text-warning me-2"></i>SmartPrice</a>
        <a href="javascript:history.back()" class="btn btn-outline-light btn-sm rounded-pill"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
    </div>
</nav>

<div class="container py-4 flex-grow-1">
    
    <div class="row bg-white rounded-4 shadow-sm p-4 mb-4">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="product-gallery rounded-4 shadow-sm">
                <i class="fas fa-box-open fa-7x text-muted opacity-25"></i>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-2 gap-2">
                <span class="badge bg-danger rounded-pill px-3 py-2"><i class="fas fa-fire me-1"></i> Giá Tốt Nhất</span>
                <?php if($diffAvg > 5): ?>
                    <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-arrow-down me-1"></i> Giảm mạnh <?php echo round($diffAvg); ?>%</span>
                <?php endif; ?>
                <div class="text-warning ms-auto"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i> <span class="text-muted small">(4.8/5)</span></div>
            </div>
            
            <h3 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="text-muted small mb-4"><?php echo htmlspecialchars($product['description']); ?></p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Giá Rẻ Nhất Hôm Nay</div>
                        <div class="h1 text-danger fw-bold mb-0">
                            <?php echo ($cheapest > 0) ? number_format($cheapest).' ₫' : 'Hết hàng'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="p-3 rounded-4 border <?php echo ($diffAvg > 0) ? 'bg-success text-white' : 'bg-warning text-dark'; ?> h-100 d-flex flex-column justify-content-center">
                        <h6 class="fw-bold mb-1"><i class="fas fa-robot me-2"></i>AI Phân Tích Giá:</h6>
                        <?php if($diffAvg > 0): ?>
                            <div class="small mb-2">Giá hiện tại đang thấp hơn mức trung bình (<?php echo number_format($avgPrice); ?>đ).</div>
                            <h5 class="fw-bold mb-0"><i class="fas fa-shopping-cart me-2"></i>Khuyên dùng: NÊN MUA NGAY</h5>
                        <?php else: ?>
                            <div class="small mb-2">Giá đang ở mức cao hoặc bằng trung bình.</div>
                            <h5 class="fw-bold mb-0"><i class="fas fa-hand-paper me-2"></i>Khuyên dùng: NÊN CHỜ THÊM</h5>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                <h5 class="fw-bold mb-4"><i class="fas fa-store text-primary me-2"></i>So Sánh Nơi Bán</h5>
                <div class="table-responsive">
                    <table class="table comparison-table align-middle border-light">
                        <tbody>
                            <?php foreach ($platforms as $index => $p): 
                                $isCheapest = ($index == 0 && $p['current_price'] > 0);
                                $diffFromMin = ($p['current_price'] - $cheapest);
                                
                                $logo = '';
                                if ($p['platform_name'] == 'Tiki') $logo = 'https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png';
                                if ($p['platform_name'] == 'Shopee') $logo = 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg';
                                if ($p['platform_name'] == 'Lazada') $logo = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png';
                            ?>
                            <tr class="<?php echo $isCheapest ? 'cheapest-row shadow-sm' : ''; ?> border-bottom">
                                <td class="py-3 px-3 rounded-start">
                                    <img src="<?php echo $logo; ?>" class="plat-logo">
                                </td>
                                <td class="py-3">
                                    <div class="fs-5 fw-bold <?php echo $isCheapest ? 'text-danger' : 'text-dark'; ?>">
                                        <?php echo number_format($p['current_price']); ?> ₫
                                    </div>
                                    <?php if($isCheapest): ?>
                                        <span class="badge bg-danger small mt-1">Rẻ nhất</span>
                                    <?php elseif($diffFromMin > 0): ?>
                                        <span class="text-muted small">+<?php echo number_format($diffFromMin); ?> ₫</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-end rounded-end px-3">
                                    <a href="<?php echo htmlspecialchars($p['product_url']); ?>" target="_blank" class="btn <?php echo $isCheapest ? 'btn-buy px-4' : 'btn-outline-primary'; ?> rounded-pill">
                                        Tới Nơi Bán <i class="fas fa-chevron-right ms-1 small"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-line text-success me-2"></i>Lịch Sử Giá</h5>
                    <div class="btn-group rounded-pill shadow-sm" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="updateChartData(7)">7 Ngày</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateChartData(30)">30 Ngày</button>
                    </div>
                </div>
                
                <div class="row text-center mb-4 border-bottom pb-3 g-2">
                    <div class="col-4"><div class="text-muted small">Cao nhất</div><div class="fw-bold text-dark fs-5"><?php echo number_format($priceStats['max_price'] ?? 0); ?>đ</div></div>
                    <div class="col-4 border-start border-end"><div class="text-muted small">Thấp nhất</div><div class="fw-bold text-danger fs-5"><?php echo number_format($priceStats['min_price'] ?? 0); ?>đ</div></div>
                    <div class="col-4"><div class="text-muted small">Trung bình</div><div class="fw-bold text-primary fs-5"><?php echo number_format($priceStats['avg_price'] ?? 0); ?>đ</div></div>
                </div>

                <div style="height: 350px;">
                    <canvas id="priceHistoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="bg-white rounded-4 shadow-sm p-4 border-top border-warning border-5 mb-4 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10 p-3"><i class="fas fa-bell fa-6x text-warning"></i></div>
                <h5 class="fw-bold text-dark mb-1 position-relative z-1">Săn Sale Tự Động</h5>
                <p class="text-muted small mb-4 position-relative z-1">Nhận Email ngay khi giá giảm đến mức kỳ vọng.</p>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="index.php?role=user&controller=product&action=setAlert" method="POST" class="position-relative z-1">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <label class="fw-bold small mb-2">Nhập giá bạn muốn mua:</label>
                        <div class="input-group mb-3 shadow-sm rounded-3">
                            <span class="input-group-text bg-light border-0"><i class="fas fa-money-bill-wave text-success"></i></span>
                            <input type="text" id="targetPriceInput" name="target_price" class="form-control border-0 fw-bold text-primary fs-5" 
                                   value="<?php echo isset($userAlert) && $userAlert ? number_format($userAlert['target_price'], 0, ',', '.') : number_format($cheapest); ?>" required autocomplete="off">
                            <span class="input-group-text bg-light border-0 fw-bold">₫</span>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-light flex-fill fw-bold text-danger" onclick="setPresetPrice(0.95)">-5%</button>
                            <button type="button" class="btn btn-sm btn-light flex-fill fw-bold text-danger" onclick="setPresetPrice(0.90)">-10%</button>
                            <button type="button" class="btn btn-sm btn-light flex-fill fw-bold text-danger" onclick="setPresetPrice(0.80)">-20%</button>
                        </div>

                        <div class="alert alert-success py-2 px-3 small border-0 mb-4" id="savingInsight">
                            <i class="fas fa-piggy-bank me-2"></i>Tiết kiệm được: <b id="savingAmount">0 ₫</b>
                        </div>

                        <?php if(isset($userAlert) && $userAlert): ?>
                            <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill text-dark shadow-sm mb-2 py-2">Cập Nhật Mục Tiêu</button>
                            <a href="index.php?role=user&controller=product&action=removeAlert&id=<?php echo $product['id']; ?>" class="btn btn-light w-100 fw-bold rounded-pill text-danger py-2">Hủy Theo Dõi</a>
                        <?php else: ?>
                            <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill text-dark shadow-sm py-2"><i class="fas fa-bell me-2"></i>Bật Theo Dõi Giá</button>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4 position-relative z-1">
                        <i class="fas fa-lock fa-3x text-muted mb-3 opacity-50"></i>
                        <p class="small text-muted mb-3">Đăng nhập để sử dụng tính năng theo dõi giá tự động thông minh.</p>
                        <a href="index.php?role=user&controller=auth&action=login" class="btn btn-warning fw-bold rounded-pill w-100">Đăng Nhập Ngay</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-4 shadow-sm p-4">
                <h6 class="fw-bold mb-3">Tại sao chọn SmartPrice?</h6>
                <div class="d-flex align-items-start mb-3">
                    <i class="fas fa-bolt text-warning fs-5 me-3 mt-1"></i>
                    <div><div class="fw-bold small">Dữ liệu Real-time</div><div class="text-muted" style="font-size: 0.8rem;">Cập nhật giá liên tục từ các hệ thống sàn TMĐT.</div></div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <i class="fas fa-shield-alt text-success fs-5 me-3 mt-1"></i>
                    <div><div class="fw-bold small">Tuyệt đối Chính xác</div><div class="text-muted" style="font-size: 0.8rem;">Loại bỏ chiêu trò tăng giá ảo của nhà bán.</div></div>
                </div>
            </div>

        </div>
    </div>

    <?php if(!empty($relatedProducts)): ?>
    <div class="mt-5">
        <h4 class="fw-bold mb-4"><i class="fas fa-tags text-primary me-2"></i>Sản Phẩm Tương Tự</h4>
        <div class="row g-4">
            <?php foreach($relatedProducts as $rp): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $rp['id']; ?>" class="text-decoration-none">
                    <div class="bg-white rounded-4 shadow-sm shadow-hover p-3 h-100 border border-light">
                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center mb-3" style="height: 150px;">
                            <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                        </div>
                        <h6 class="fw-bold text-dark text-truncate mb-2"><?php echo htmlspecialchars($rp['name']); ?></h6>
                        <div class="text-danger fw-bold fs-5"><?php echo ($rp['min_price'] > 0) ? number_format($rp['min_price']).' ₫' : 'Đang cập nhật'; ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
/* --- 1. LOGIC BIỂU ĐỒ (CHART.JS NÂNG CẤP) --- */
const rawData = <?php echo json_encode($priceHistory); ?>;
const dailyData = {};
rawData.sort((a, b) => new Date(a.scraped_at) - new Date(b.scraped_at));

rawData.forEach(item => {
    let dateOnly = item.scraped_at.split(' ')[0]; 
    if (!dailyData[dateOnly]) dailyData[dateOnly] = { 'Tiki': null, 'Shopee': null, 'Lazada': null };
    dailyData[dateOnly][item.platform_name] = item.price;
});

const rawLabels = Object.keys(dailyData);
const displayLabels = rawLabels.map(dateStr => dateStr.split('-').reverse().slice(0,2).join('/'));

const tikiData = rawLabels.map(date => dailyData[date]['Tiki']);
const shopeeData = rawLabels.map(date => dailyData[date]['Shopee']);
const lazadaData = rawLabels.map(date => dailyData[date]['Lazada']);

let priceChart;
function initChart(days = null) {
    let labels = displayLabels;
    let d1 = tikiData, d2 = shopeeData, d3 = lazadaData;

    // Filter Logic (Giả lập cắt array dựa theo số ngày)
    if(days) {
        labels = labels.slice(-days);
        d1 = d1.slice(-days); d2 = d2.slice(-days); d3 = d3.slice(-days);
    }

    const ctx = document.getElementById('priceHistoryChart').getContext('2d');
    if(priceChart) priceChart.destroy(); // Hủy chart cũ trước khi vẽ lại
    
    priceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'Tiki', data: d1, borderColor: '#189eff', backgroundColor: 'rgba(24, 158, 255, 0.1)', fill: true, tension: 0.4, borderWidth: 3, pointBackgroundColor: '#fff' },
                { label: 'Shopee', data: d2, borderColor: '#ee4d2d', backgroundColor: 'rgba(238, 77, 45, 0.1)', fill: true, tension: 0.4, borderWidth: 3, pointBackgroundColor: '#fff' },
                { label: 'Lazada', data: d3, borderColor: '#00008b', backgroundColor: 'rgba(0, 0, 139, 0.1)', fill: true, tension: 0.4, borderWidth: 3, pointBackgroundColor: '#fff' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false }, // Tooltip Upgrade: Hover ở bất cứ đâu trên trục dọc cũng hiện info
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, titleFont: { size: 14 }, bodyFont: { size: 13 },
                    callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${new Intl.NumberFormat('vi-VN').format(ctx.parsed.y)} ₫` }
                },
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
            },
            scales: {
                y: { grid: { borderDash: [5, 5] }, ticks: { callback: val => new Intl.NumberFormat('vi-VN').format(val) + ' ₫' } },
                x: { grid: { display: false } }
            }
        }
    });
}
initChart(); // Vẽ lần đầu

function updateChartData(days) {
    // Chuyển UI button active
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    initChart(days);
}

/* --- 2. LOGIC ALERT (TÍNH TOÁN TIẾT KIỆM) --- */
const cheapestPrice = <?php echo $cheapest; ?>;
const priceInput = document.getElementById('targetPriceInput');
const savingText = document.getElementById('savingAmount');

function formatCurrency(num) { return parseInt(num, 10).toLocaleString('vi-VN').replace(/,/g, '.'); }

function calculateSaving() {
    let target = parseInt(priceInput.value.replace(/[^0-9]/g, '')) || 0;
    if(target < cheapestPrice && target > 0) {
        savingText.innerText = formatCurrency(cheapestPrice - target) + ' ₫';
        savingText.classList.replace('text-danger', 'text-success');
    } else {
        savingText.innerText = "0 ₫ (Chọn giá thấp hơn)";
        savingText.classList.replace('text-success', 'text-danger');
    }
}

// Lắng nghe sự kiện gõ phím
if (priceInput) {
    priceInput.addEventListener('input', function(e) {
        let val = this.value.replace(/[^0-9]/g, '');
        if (val !== '') this.value = formatCurrency(val);
        calculateSaving();
    });
    calculateSaving(); // Chạy lần đầu
}

// Preset nhanh
function setPresetPrice(multiplier) {
    let newPrice = Math.floor(cheapestPrice * multiplier);
    priceInput.value = formatCurrency(newPrice);
    calculateSaving();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>