<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product['name']; ?> - Lịch sử giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-stats { border: none; border-radius: 15px; }
        .platform-badge { font-size: 0.8rem; padding: 5px 12px; border-radius: 20px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="fas fa-arrow-left me-2"></i> Quay lại tìm kiếm</a>
    </div>
</nav>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-stats shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="fw-bold mb-3"><?php echo $product['name']; ?></h2>
                    <p class="text-muted mb-4"><?php echo $product['description']; ?></p>
                    <hr>
                    <h5 class="fw-bold mb-3">Giá hiện tại trên các sàn:</h5>
                    <?php foreach ($platforms as $p): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                        <div>
                            <span class="badge <?php echo ($p['platform_name']=='Tiki')?'bg-primary':'bg-danger'; ?> platform-badge mb-1">
                                <?php echo $p['platform_name']; ?>
                            </span>
                            <div class="small text-muted">
                                Cập nhật: <?php echo ($p['last_scraped_at']) ? date('d/m H:i', strtotime($p['last_scraped_at'])) : 'Đang chờ quét...'; ?>
                            </div>                        </div>
                        <div class="text-end">
                            <div class="h4 fw-bold mb-0 text-dark"><?php echo number_format($p['current_price']); ?>đ</div>
                            <a href="<?php echo $p['product_url']; ?>" target="_blank" class="small text-decoration-none">Tới nơi bán <i class="fas fa-external-link-alt ms-1"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-stats shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-area me-2 text-primary"></i>Biểu đồ biến động giá</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="priceHistoryChart" style="width: 100%; height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Nhận dữ liệu từ PHP
const rawData = <?php echo json_encode($priceHistory); ?>;

// Xử lý dữ liệu cho Chart.js
// 1. Lấy danh sách các mốc thời gian duy nhất (Labels)
const labels = [...new Set(rawData.map(item => {
    let date = new Date(item.scraped_at);
    return date.getDate() + '/' + (date.getMonth() + 1) + ' ' + date.getHours() + ':' + date.getMinutes();
}))];

// 2. Tách dữ liệu theo từng sàn
const tikiData = rawData.filter(i => i.platform_name === 'Tiki').map(i => i.price);
const shopeeData = rawData.filter(i => i.platform_name === 'Shopee').map(i => i.price);

const ctx = document.getElementById('priceHistoryChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Giá Tiki (đ)',
                data: tikiData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5
            },
            {
                label: 'Giá Shopee (đ)',
                data: shopeeData,
                borderColor: '#ee4d2d',
                backgroundColor: 'rgba(238, 77, 45, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) { return value.toLocaleString() + ' đ'; }
                }
            }
        }
    }
});
</script>

</body>
</html>