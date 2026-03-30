<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Cảnh báo Giá - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Cấu hình Sidebar */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; border-bottom: 1px solid #34495e; }
        .nav-link { color: #bdc3c7; padding: 12px 20px; transition: 0.3s; margin: 5px 15px; border-radius: 8px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        
        /* Bảng hiển thị */
        .table-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .table thead th { background-color: #f8f9fa; color: #2c3e50; font-weight: 700; border-bottom: 2px solid #e9ecef; }
        .table tbody td { vertical-align: middle; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="fas fa-envelope-open-text text-primary me-2"></i>Danh Sách Cảnh Báo Giá</h2>
                <p class="text-muted">Theo dõi các yêu cầu nhận thông báo sập sàn của Khách hàng.</p>
            </div>
            <span class="badge bg-primary fs-6 py-2 px-3 shadow-sm rounded-pill">Tổng cộng: <?php echo count($alerts); ?> Yêu cầu</span>
        </div>

        <div class="card table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Khách hàng (Email)</th>
                                <th style="width: 30%;">Sản phẩm theo dõi</th>
                                <th class="text-center">Giá kỳ vọng</th>
                                <th class="text-center">Giá rẻ nhất hiện tại</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end pe-4">Ngày thiết lập</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($alerts)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3"></i>
                                        <h5>Chưa có khách hàng nào đặt cảnh báo!</h5>
                                    </td>
                                </tr>
                            <?php else: 
                                $stt = 1;
                                foreach ($alerts as $a): 
                                    // Kiểm tra xem giá hiện tại đã đạt kỳ vọng chưa
                                    $is_reached = ($a['current_min_price'] > 0 && $a['current_min_price'] <= $a['target_price']);
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted"><?php echo $stt++; ?></td>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($a['email']); ?></span></td>
                                    <td>
                                        <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $a['p_id']; ?>" target="_blank" class="text-decoration-none fw-bold" title="<?php echo htmlspecialchars($a['product_name']); ?>">
                                            <?php 
                                                $name = htmlspecialchars($a['product_name']);
                                                echo (mb_strlen($name) > 40) ? mb_substr($name, 0, 40) . '...' : $name; 
                                            ?>
                                        </a>
                                    </td>
                                    <td class="text-center fw-bold text-warning"><?php echo number_format($a['target_price']); ?>đ</td>
                                    <td class="text-center fw-bold <?php echo $is_reached ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($a['current_min_price'] > 0) ? number_format($a['current_min_price']).'đ' : 'Đang chờ quét'; ?>
                                        <?php if($is_reached && $a['is_notified'] == 0): ?>
                                            <br><span class="badge bg-danger mt-1" style="font-size: 0.65rem;">Sẵn sàng báo Sale</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($a['is_notified'] == 1): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i>Đã gửi Email</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="fas fa-hourglass-half me-1"></i>Đang canh giá</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 text-muted small">
                                        <?php echo date('d/m/Y H:i', strtotime($a['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
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