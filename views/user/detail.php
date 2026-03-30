<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - Lịch sử giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-stats { border: none; border-radius: 15px; }
        .platform-badge { font-size: 0.8rem; padding: 5px 12px; border-radius: 20px; }
        .bg-soft-warning { background-color: #fffbf0; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-search-dollar text-warning me-2"></i>PRICE COMPARISON</a>
        
        <div class="d-flex align-items-center">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a class="btn btn-outline-light border-0 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg me-1"></i> 
                        <span class="fw-bold"><?php echo explode('@', $_SESSION['user_email'])[0]; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <li><a class="dropdown-item fw-bold text-primary" href="index.php?role=admin&controller=dashboard"><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="#"><i class="fas fa-bell me-2"></i>Cảnh báo giá của tôi</a></li>
                        <li><a class="dropdown-item text-danger" href="index.php?role=user&controller=auth&action=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="index.php?role=user&controller=auth&action=login" class="btn btn-outline-light btn-sm me-2 rounded-pill px-3">Đăng nhập</a>
                <a href="index.php?role=user&controller=auth&action=register" class="btn btn-warning btn-sm fw-bold rounded-pill px-3 text-dark">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="bg-white shadow-sm mb-4 py-2">
    <div class="container">
        <a href="javascript:history.back()" class="text-decoration-none text-secondary fw-bold">
            <i class="fas fa-arrow-left me-2"></i> Quay lại trang trước
        </a>
    </div>
</div>

<div class="container pb-5 flex-grow-1">
    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="card card-stats shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p class="text-muted small mb-4 line-clamp-2" title="<?php echo htmlspecialchars($product['description']); ?>"><?php echo htmlspecialchars($product['description']); ?></p>
                    <hr>
                    <h6 class="fw-bold mb-3 text-dark">Giá hiện tại trên các sàn:</h6>
                    <?php foreach ($platforms as $p): 
                        $badge_class = 'bg-secondary';
                        $custom_style = '';
                        if ($p['platform_name'] == 'Tiki') $badge_class = 'bg-primary';
                        if ($p['platform_name'] == 'Shopee') $badge_class = 'bg-danger';
                        if ($p['platform_name'] == 'Lazada') {
                            $badge_class = 'text-white';
                            $custom_style = 'background-color: #00008b;';
                        }
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                        <div>
                            <span class="badge <?php echo $badge_class; ?> platform-badge mb-1" style="<?php echo $custom_style; ?>">
                                <?php echo htmlspecialchars($p['platform_name']); ?>
                            </span>
                            <div class="small text-muted">
                                Cập nhật: <?php echo ($p['last_scraped_at']) ? date('d/m H:i', strtotime($p['last_scraped_at'])) : 'Đang chờ quét...'; ?>
                            </div>                        
                        </div>
                        <div class="text-end">
                            <div class="h5 fw-bold mb-0 text-dark"><?php echo number_format($p['current_price']); ?>đ</div>
                            <a href="<?php echo htmlspecialchars($p['product_url']); ?>" target="_blank" class="small text-decoration-none">Tới nơi bán <i class="fas fa-external-link-alt ms-1"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm border-warning" style="border-radius: 15px; border-width: 2px;">
                <div class="card-body p-4 text-center bg-soft-warning" style="border-radius: 13px;">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-bell fa-3x"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Săn Sale Tự Động</h5>
                    <p class="text-muted small mb-4">Nhập mức giá "Chốt đơn" của bạn. Hệ thống sẽ tự động gửi Email ngay khi giá sập sàn!</p>
                    
                    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'alert_success'): ?>
                        <div class="alert alert-success py-2 small fw-bold">
                            <i class="fas fa-check-circle me-1"></i> Đã ghi nhận mức giá của bạn!
                        </div>
                    <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'alert_removed'): ?>
                        <div class="alert alert-secondary py-2 small fw-bold">
                            <i class="fas fa-bell-slash me-1"></i> Đã hủy nhận email báo giá!
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="index.php?role=user&controller=product&action=setAlert" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
                                <input type="text" id="targetPriceInput" name="target_price" class="form-control border-0 px-3" 
                                       placeholder="Ví dụ: 4.500.000" 
                                       value="<?php echo isset($userAlert) && $userAlert ? number_format($userAlert['target_price'], 0, ',', '.') : ''; ?>" required autocomplete="off">
                                <span class="input-group-text bg-white border-0 fw-bold text-muted">VNĐ</span>
                            </div>

                            <?php if(isset($userAlert) && $userAlert): ?>
                                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill text-dark shadow-sm mb-2">
                                    <i class="fas fa-sync-alt me-2"></i>Cập nhật giá
                                </button>
                                <a href="index.php?role=user&controller=product&action=removeAlert&id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline-danger w-100 fw-bold rounded-pill shadow-sm"
                                   onclick="return confirm('Bạn có chắc chắn muốn hủy nhận thông báo cho sản phẩm này không?');">
                                    <i class="fas fa-bell-slash me-2"></i>Hủy theo dõi
                                </a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill text-dark shadow-sm">
                                    <i class="fas fa-eye me-2"></i>Bật Theo Dõi Giá
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <a href="index.php?role=user&controller=auth&action=login" class="btn btn-outline-warning w-100 fw-bold rounded-pill text-dark shadow-sm">
                            <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập để sử dụng
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-stats shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-area me-2 text-primary"></i>Biểu đồ biến động giá 3 Sàn</h5>
                </div>
                <div class="card-body p-4">
                    <div style="height: 450px;">
                        <canvas id="priceHistoryChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <small class="text-white-50">&copy; <?php echo date("Y"); ?> DoAnTotNghiep - Hệ Thống So Sánh Giá Đa Sàn</small>
    </div>
</footer>

<script>
// Nhận dữ liệu từ PHP
const rawData = <?php echo json_encode($priceHistory); ?>;

// 1. GOM NHÓM DỮ LIỆU THEO NGÀY (Lọc bỏ giờ phút)
const dailyData = {};

// Đảm bảo dữ liệu được sắp xếp từ cũ đến mới
rawData.sort((a, b) => new Date(a.scraped_at) - new Date(b.scraped_at));

rawData.forEach(item => {
    // Cắt chuỗi lấy phần ngày (Từ "2026-03-18 10:30:00" -> "2026-03-18")
    let dateOnly = item.scraped_at.split(' ')[0]; 
    
    // Nếu ngày này chưa có trong danh sách, tạo mới form trống
    if (!dailyData[dateOnly]) {
        dailyData[dateOnly] = { 'Tiki': null, 'Shopee': null, 'Lazada': null };
    }
    
    // Ghi đè giá trị. Do đã sort từ cũ đến mới ở trên, 
    // mức giá ghi đè cuối cùng sẽ là giá chốt của ngày hôm đó!
    dailyData[dateOnly][item.platform_name] = item.price;
});

// 2. TẠO TRỤC X (Mốc thời gian rút gọn)
const rawLabels = Object.keys(dailyData);

// Format lại thành dạng Ngày/Tháng (VD: 18/03)
const displayLabels = rawLabels.map(dateStr => {
    let parts = dateStr.split('-'); // Trả về mảng [Năm, Tháng, Ngày]
    return parts[2] + '/' + parts[1];
});

// 3. TẠO TRỤC Y (Dữ liệu 3 sàn)
const tikiData = rawLabels.map(date => dailyData[date]['Tiki']);
const shopeeData = rawLabels.map(date => dailyData[date]['Shopee']);
const lazadaData = rawLabels.map(date => dailyData[date]['Lazada']);

// 4. VẼ BIỂU ĐỒ
const ctx = document.getElementById('priceHistoryChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: displayLabels,
        datasets: [
            {
                label: 'Giá Tiki',
                data: tikiData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 8,
                spanGaps: true // Tự nối nét đứt nếu ngày đó sàn bị lỗi không cào được
            },
            {
                label: 'Giá Shopee',
                data: shopeeData,
                borderColor: '#ee4d2d',
                backgroundColor: 'rgba(238, 77, 45, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 8,
                spanGaps: true
            },
            {
                label: 'Giá Lazada',
                data: lazadaData,
                borderColor: '#00008b',
                backgroundColor: 'rgba(0, 0, 139, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 8,
                spanGaps: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            tooltip: { 
                mode: 'index', 
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) { label += ': '; }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) { return new Intl.NumberFormat('vi-VN').format(value) + ' đ'; }
                }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Xử lý tự động thêm dấu chấm phân cách hàng nghìn khi gõ giá tiền
    const priceInput = document.getElementById('targetPriceInput');
    if (priceInput) {
        priceInput.addEventListener('input', function(e) {
            // 1. Lấy giá trị hiện tại và loại bỏ tất cả các ký tự không phải là số (chữ cái, dấu chấm, phẩy...)
            let value = this.value.replace(/[^0-9]/g, '');
            
            // 2. Nếu có giá trị số, định dạng lại theo chuẩn Việt Nam (thêm dấu chấm)
            if (value !== '') {
                value = parseInt(value, 10).toLocaleString('vi-VN');
            }
            
            // 3. Gán ngược lại vào ô input
            this.value = value;
        });
    }
</script>
</body>
</html>