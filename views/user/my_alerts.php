<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Săn Sale - Price Comparison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .alert-card { border: none; border-radius: 20px; overflow: hidden; transition: all 0.3s ease; }
        .alert-card:hover { transform: translateY(-7px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .img-container { height: 220px; width: 100%; background-color: #fff; display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid #f0f0f0; }
        .img-container img { max-height: 100%; max-width: 100%; object-fit: contain; padding: 15px; }
        .empty-img-icon { font-size: 5rem; color: #e9ecef; }
        
        /* Nhãn dán nổi bật */
        .status-badge { position: absolute; top: 15px; left: 15px; z-index: 10; padding: 6px 15px; font-weight: bold; border-radius: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .badge-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
        .badge-waiting { background: rgba(255, 255, 255, 0.9); color: #6c757d; backdrop-filter: blur(5px); border: 1px solid #e9ecef; }
        
        .price-box { background: #f8f9fa; border-radius: 12px; padding: 12px; border: 1px dashed #ced4da; }
        .target-price { color: #fd7e14; font-size: 1.3rem; font-weight: 800; }
        .current-price { color: #212529; font-size: 1.1rem; font-weight: 700; }
        .price-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; margin-bottom: 3px; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<div class="bg-dark text-white py-3 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="text-white text-decoration-none fw-bold fs-5"><i class="fas fa-arrow-left me-2"></i>Quay lại trang chủ</a>
        <span class="fw-bold"><i class="fas fa-bullseye text-warning me-2"></i>TRUNG TÂM SĂN SALE</span>
    </div>
</div>

<div class="container my-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-heart text-danger me-2"></i>Sản Phẩm Đang Theo Dõi</h2>
            <p class="text-muted mb-0">Hệ thống sẽ tự động gửi Email khi giá chạm mốc bạn mong muốn.</p>
        </div>
        <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm d-none d-md-block">
            <i class="fas fa-search me-2"></i>Tìm thêm DEAL
        </a>
    </div>

    <?php if (empty($alerts)): ?>
        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-body py-5 text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" alt="Empty" width="150" class="mb-4 opacity-50">
                <h4 class="fw-bold text-dark">Danh sách trống trơn!</h4>
                <p class="text-muted mx-auto" style="max-width: 400px;">Bạn chưa đưa sản phẩm nào vào tầm ngắm. Hãy tìm kiếm sản phẩm và thiết lập mức giá "chốt đơn" ngay nhé.</p>
                <a href="index.php" class="btn btn-warning rounded-pill px-5 py-2 fw-bold mt-2 shadow-sm">
                    <i class="fas fa-rocket me-2"></i>Đi Săn Sale Thôi
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($alerts as $item): 
                $isReached = ($item['min_price'] > 0 && $item['min_price'] <= $item['target_price']);
            ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 alert-card bg-white position-relative">
                        
                        <div class="img-container">
                            <?php if($isReached): ?>
                                <span class="status-badge badge-success"><i class="fas fa-meteor me-1"></i>Đã Sập Sàn</span>
                            <?php else: ?>
                                <span class="status-badge badge-waiting"><i class="fas fa-hourglass-half me-1"></i>Đang Canh Giá</span>
                            <?php endif; ?>
                            
                            <?php if(!empty($item['thumbnail_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" alt="Ảnh sản phẩm">
                            <?php else: ?>
                                <i class="fas fa-box-open empty-img-icon"></i>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h6 class="fw-bold mb-3 line-clamp-2 text-dark" title="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </h6>
                            
                            <div class="mt-auto">
                                <div class="price-box mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <div class="price-label">Giá mong muốn</div>
                                        <div class="target-price"><?php echo number_format($item['target_price']); ?>đ</div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price-label">Rẻ nhất hiện tại</div>
                                        <div class="current-price <?php echo $isReached ? 'text-success' : ''; ?>">
                                            <?php echo $item['min_price'] ? number_format($item['min_price']).'đ' : '<span class="badge bg-secondary">Chờ quét...</span>'; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-8">
                                        <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $item['product_id']; ?>" class="btn btn-dark w-100 rounded-pill fw-bold" style="font-size: 0.9rem;">
                                            Xem Biểu Đồ
                                        </a>
                                    </div>
                                    <div class="col-4">
                                        <a href="index.php?role=user&controller=product&action=removeAlert&id=<?php echo $item['product_id']; ?>" 
                                           class="btn btn-outline-danger w-100 rounded-pill" 
                                           onclick="return confirm('Ngừng nhận thông báo cho sản phẩm này?');" title="Hủy theo dõi">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <small class="text-white-50">&copy; <?php echo date("Y"); ?> DoAnTotNghiep - Hệ Thống So Sánh Giá Đa Sàn</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>