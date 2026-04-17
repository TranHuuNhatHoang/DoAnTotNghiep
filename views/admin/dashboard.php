<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Đồng bộ CSS Sidebar */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; border-bottom: 1px solid #34495e;}
        .nav-link { color: #bdc3c7; padding: 12px 20px; border-radius: 8px; transition: 0.3s; margin: 5px 15px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        
        .stat-card { border: none; border-radius: 15px; transition: 0.3s; color: white; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 3rem; opacity: 0.4; }
        .bg-primary-grad { background: linear-gradient(45deg, #4e73df, #224abe); }
        .bg-success-grad { background: linear-gradient(45deg, #1cc88a, #13855c); }
        .bg-warning-grad { background: linear-gradient(45deg, #f6c23e, #dda20a); }
        .bg-danger-grad { background: linear-gradient(45deg, #e74a3b, #be2617); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-1">Bảng Điều Khiển Hệ Thống</h2>
                <p class="text-muted">Tổng quan dữ liệu và hiệu suất hoạt động.</p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-primary-grad shadow">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-2">Tổng Sản Phẩm</h6>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_products']); ?></h2>
                        </div>
                        <i class="fas fa-box stat-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-success-grad shadow">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-2">Danh mục</h6>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_categories']); ?></h2>
                        </div>
                        <i class="fas fa-list stat-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-warning-grad shadow">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-2">Đang theo dõi giá</h6>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_alerts']); ?></h2>
                        </div>
                        <i class="fas fa-bell stat-icon"></i>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-danger-grad shadow">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-2">Người dùng</h6>
                            <h2 class="fw-bold mb-0"><?php echo number_format($stats['total_users']); ?></h2>
                        </div>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>