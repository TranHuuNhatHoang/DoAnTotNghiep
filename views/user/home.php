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
        .price-val { font-size: 1.25rem; font-weight: 800; }
        .platform-logo { height: 20px; object-fit: contain; margin-bottom: 5px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">PRICE COMPARISON</a>
        <a href="index.php?role=admin&controller=dashboard" class="btn btn-outline-light btn-sm">Admin Panel</a>
    </div>
</nav>

<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3">Tìm Kiếm & So Sánh Giá</h1>
        <p class="lead mb-4">Tiết kiệm hơn khi biết rõ giá thị trường từ Tiki và Shopee</p>
        <div class="row justify-content-center">
            <div class="col-md-7">
                <form action="index.php" method="GET" class="input-group input-group-lg shadow-lg">
                    <input type="hidden" name="role" value="user">
                    <input type="hidden" name="controller" value="product">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="keyword" class="form-control border-0" placeholder="Bạn muốn mua gì hôm nay?" value="<?php echo htmlspecialchars($keyword ?? ''); ?>" required>
                    <button class="btn btn-warning px-4" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <?php if (isset($keyword)): ?>
        <h3 class="mb-4">Kết quả cho: "<span class="text-primary"><?php echo htmlspecialchars($keyword); ?></span>"</h3>
        <?php if (empty($products)): ?>
            <div class="alert alert-info py-5 text-center">
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>Rất tiếc, chúng tôi chưa có dữ liệu cho sản phẩm này.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $p): ?>
                <div class="col-md-6 mb-4">
                    <div class="card product-card shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1"><?php echo $p['name']; ?></h5>
                            <p class="text-muted small mb-4"><?php echo $p['description']; ?></p>
                            
                            <div class="row g-0 text-center border-top pt-3">
                                <div class="col-6 border-end">
                                    <div class="text-primary small fw-bold">TIKI</div>
                                    <div class="price-val text-primary"><?php echo $p['tiki_price'] ? number_format($p['tiki_price']).'đ' : 'N/A'; ?></div>
                                    <a href="<?php echo $p['tiki_url']; ?>" target="_blank" class="btn btn-sm btn-link text-decoration-none">Đến nơi bán <i class="fas fa-external-link-alt"></i></a>
                                </div>
                                <div class="col-6">
                                    <div class="text-danger small fw-bold">SHOPEE</div>
                                    <div class="price-val text-danger"><?php echo $p['shopee_price'] ? number_format($p['shopee_price']).'đ' : 'N/A'; ?></div>
                                    <a href="<?php echo $p['shopee_url']; ?>" target="_blank" class="btn btn-sm btn-link text-decoration-none text-danger">Đến nơi bán <i class="fas fa-external-link-alt"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 text-center pb-3">
                            <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $p['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-4">
                                <i class="fas fa-chart-line me-1"></i> Xem lịch sử giá
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<footer class="bg-white py-4 mt-5 border-top">
    <div class="container text-center text-muted">
        <small>&copy; 2026 DoAnTotNghiep - Hệ Thống So Sánh Giá Đa Sàn</small>
    </div>
</footer>

</body>
</html>