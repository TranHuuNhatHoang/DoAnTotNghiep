<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>So Sánh Giá Đa Sàn - DoAnTotNghiep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0; color: white; }
        .product-card { border: none; border-radius: 15px; transition: 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .price-val { font-size: 1.15rem; font-weight: 800; }
        .lazada-color { color: #00008b !important; }
    </style>
</head>
<body class="bg-light">

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

<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3">Tìm Kiếm & So Sánh Giá</h1>
        <p class="lead mb-4">Tiết kiệm hơn khi biết rõ giá thị trường từ Tiki, Shopee và Lazada</p>
        <div class="row justify-content-center">
            <div class="col-md-7">
                <form action="index.php" method="GET" class="input-group input-group-lg shadow-lg">
                    <input type="hidden" name="role" value="user">
                    <input type="hidden" name="controller" value="product">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="keyword" class="form-control border-0 px-4" placeholder="Bạn muốn mua gì hôm nay?" value="<?php echo isset($keyword) ? htmlspecialchars($keyword) : ''; ?>" required>
                    <button class="btn btn-warning px-4 fw-bold" type="submit"><i class="fas fa-search me-2"></i>Tìm giá</button>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    
    <?php if (isset($keyword)): ?>
        <h3 class="mb-4">Kết quả cho: "<span class="text-primary"><?php echo htmlspecialchars($keyword); ?></span>"</h3>
        <?php if (empty($products)): ?>
            <div class="alert alert-info py-5 text-center bg-white border-0 shadow-sm rounded-4">
                <i class="fas fa-search-minus fa-3x mb-3 text-muted"></i>
                <h5 class="fw-bold">Chưa tìm thấy sản phẩm</h5>
                <p class="text-muted mb-0">Rất tiếc, hệ thống chưa có dữ liệu cho từ khóa này.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $p): ?>
                <div class="col-md-6">
                    <div class="card product-card shadow-sm h-100 d-flex flex-column">
                        <div class="card-body p-4 flex-grow-1">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($p['name']); ?></h5>
                            <p class="text-muted small mb-4 line-clamp-2"><?php echo htmlspecialchars($p['description']); ?></p>
                            
                            <div class="row g-0 text-center border-top pt-3 mt-auto">
                                <div class="col-4 border-end">
                                    <div class="text-primary small fw-bold">TIKI</div>
                                    <div class="price-val text-primary"><?php echo $p['tiki_price'] ? number_format($p['tiki_price']).'đ' : 'N/A'; ?></div>
                                </div>
                                <div class="col-4 border-end">
                                    <div class="text-danger small fw-bold">SHOPEE</div>
                                    <div class="price-val text-danger"><?php echo $p['shopee_price'] ? number_format($p['shopee_price']).'đ' : 'N/A'; ?></div>
                                </div>
                                <div class="col-4">
                                    <div class="lazada-color small fw-bold">LAZADA</div>
                                    <div class="price-val lazada-color"><?php echo $p['lazada_price'] ? number_format($p['lazada_price']).'đ' : 'N/A'; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-0 text-center py-3">
                            <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $p['id']; ?>" class="btn btn-dark btn-sm rounded-pill px-4 shadow-sm w-100">
                                <i class="fas fa-chart-line me-2"></i> Xem lịch sử giá & Đặt cảnh báo
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <h4 class="fw-bold mb-4"><i class="fas fa-fire text-danger me-2"></i>Top Sản Phẩm Đang Theo Dõi</h4>
        <div class="row g-4">
            <?php if(!empty($trending_products)): ?>
                <?php foreach ($trending_products as $tp): ?>
                    <div class="col-md-4">
                        <div class="card product-card shadow-sm h-100 border-0">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($tp['name']); ?></h6>
                                </div>
                                <p class="text-muted small mb-3">Đang quét giá trên <?php echo $tp['total_active_links']; ?> sàn</p>
                                <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $tp['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                                    So sánh giá ngay <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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