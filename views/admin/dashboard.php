<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hệ Thống So Sánh Giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Cấu hình CSS cho Sidebar (Kế thừa cho file include) */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; transition: all 0.3s; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; }
        .nav-link { color: #bdc3c7; padding: 12px 20px; transition: 0.3s; margin: 5px 15px; border-radius: 8px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; transition: all 0.3s; }
        
        /* Cards & Tables Styling */
        .stat-card { border: none; border-radius: 15px; transition: 0.3s; border-left: 5px solid #3498db; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
        .table-card { border: none; border-radius: 15px; overflow: hidden; }
        .product-name { font-weight: 600; color: #2c3e50; }
        .badge-soft-success { background-color: #d1f2eb; color: #16a085; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Bảng Điều Khiển Hệ Thống</h2>
                <p class="text-muted">Quản lý dữ liệu và theo dõi hiệu năng Bot Crawler</p>
            </div>
            <a href="index.php?role=admin&controller=dashboard&action=add" class="btn btn-primary px-4 py-2 rounded-pill shadow">
                <i class="fas fa-plus me-2"></i>Thêm Sản Phẩm Mới
            </a>
        </div>

        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card stat-card bg-white shadow-sm p-3 h-100" style="border-left-color: #3498db;">
                    <div class="d-flex align-items-center">
                        <div class="bg-soft-primary text-primary p-3 rounded-3 me-3" style="background-color: #ebf5fb;">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Sản phẩm gốc</h6>
                            <h3 class="fw-bold mb-0"><?php echo count($products); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-white shadow-sm p-3 h-100" style="border-left-color: #2ecc71;">
                    <div class="d-flex align-items-center">
                        <div class="bg-soft-success text-success p-3 rounded-3 me-3" style="background-color: #e8f8f5;">
                            <i class="fas fa-link fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Link đang theo dõi</h6>
                            <h3 class="fw-bold mb-0"><?php echo isset($botStats['total_links']) ? $botStats['total_links'] : 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-white shadow-sm p-3 h-100" style="border-left-color: #e74c3c;">
                    <div class="d-flex align-items-center">
                        <div class="bg-soft-danger text-danger p-3 rounded-3 me-3" style="background-color: #fdedec;">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Link lỗi / Chặn</h6>
                            <h3 class="fw-bold mb-0 text-danger"><?php echo isset($botStats['error_links']) ? $botStats['error_links'] : 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card bg-white shadow-sm p-3 h-100" style="border-left-color: #f39c12;">
                    <div class="d-flex align-items-center h-100">
                        <div class="w-100">
                            <h6 class="text-muted mb-2"><i class="fas fa-chart-pie me-1"></i> Mật độ dữ liệu</h6>
                            <div class="d-flex justify-content-between small">
                                <?php if(!empty($botStats['platforms'])): ?>
                                    <?php foreach($botStats['platforms'] as $plat): ?>
                                        <span class="badge bg-secondary"><?php echo $plat['platform_name']; ?>: <?php echo $plat['count']; ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Chưa có dữ liệu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-card shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2"></i>Danh sách Sản phẩm Hệ thống</h5>
                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter me-1"></i> Lọc dữ liệu</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Tên Sản Phẩm</th>
                                <th class="text-center">Số sàn đang theo dõi</th>
                                <th>Cập nhật cuối</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-bold">#<?php echo $p['id']; ?></td>
                                    <td>
                                        <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 300px;">
                                            <?php echo htmlspecialchars(substr($p['description'], 0, 80)); ?>...
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-success rounded-pill px-3 py-2">
                                            <i class="fas fa-link me-1"></i> <?php echo $p['total_active_links']; ?> sàn
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-dark">
                                            <?php echo ($p['last_update']) ? date('d/m/Y H:i', strtotime($p['last_update'])) : '<span class="text-warning">Chưa có dữ liệu</span>'; ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded">
                                            <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-light text-primary" title="Xem biểu đồ">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <button class="btn btn-sm btn-light text-secondary" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light text-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" height="80" class="mb-3 opacity-50" alt="No Data">
                                        <p class="text-muted">Chưa có sản phẩm nào. Hãy thêm sản phẩm đầu tiên!</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>