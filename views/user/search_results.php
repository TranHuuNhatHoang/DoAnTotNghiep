<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Hệ Thống So Sánh Giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary-color: #ff4a00; --bg-gray: #f5f5fa; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Kế thừa CSS của Navbar từ trang chủ */
        .navbar-main { background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .dropdown-mega:hover .dropdown-menu { display: flex; flex-wrap: wrap; }
        .dropdown-mega .dropdown-menu { display: none; position: absolute; width: 600px; padding: 20px; border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: 0; }
        .category-item { width: 33.33%; padding: 10px; border-radius: 8px; transition: 0.2s; color: #333; text-decoration: none; display: flex; align-items: center; }
        .category-item:hover { background-color: #fff4f0; color: var(--primary-color); }

        /* Khung Lọc (Sidebar Filter) */
        .filter-sidebar { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); }
        .filter-title { font-weight: bold; text-transform: uppercase; font-size: 0.9rem; color: #555; margin-bottom: 15px; }
        
        /* Product Card UI */
        .product-card { background: white; border-radius: 12px; padding: 15px; border: 1px solid #f0f0f0; transition: all 0.3s; height: 100%; display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: transparent; }
        .product-img-wrapper { height: 180px; background: #f8f9fa; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; }
        .product-img-wrapper img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
        .product-title { font-size: 0.95rem; font-weight: 600; color: #333; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .price-min { font-size: 1.3rem; font-weight: bold; color: #ff424e; }
        .platform-prices { display: flex; justify-content: space-between; margin-top: 10px; border-top: 1px dashed #eee; padding-top: 10px; }
        .plat-icon { width: 20px; height: 20px; object-fit: contain; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-main py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3 text-primary" href="index.php">
            <i class="fas fa-search-dollar me-2"></i>Smart<span style="color: var(--primary-color);">Price</span>
        </a>
        <div class="d-flex align-items-center ms-auto">
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-home me-2"></i>Về Trang Chủ</a>
        </div>
    </div>
</nav>

<div class="bg-white border-bottom mb-4">
    <div class="container py-3">
        <div class="fw-bold fs-5 text-dark">
            <?php if(!empty($keyword)): ?>
                Kết quả tìm kiếm cho: <span class="text-primary">"<?php echo htmlspecialchars($keyword); ?>"</span>
            <?php else: ?>
                Tất cả sản phẩm
            <?php endif; ?>
        </div>
        <div class="text-muted small">Tìm thấy <?php echo count($products); ?> sản phẩm phù hợp</div>
    </div>
</div>

<div class="container mb-5">
    <form action="index.php" method="GET" id="filterForm">
        <input type="hidden" name="role" value="user">
        <input type="hidden" name="controller" value="product">
        <input type="hidden" name="action" value="search">
        <input type="hidden" name="keyword" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">

        <div class="row">
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="filter-sidebar">
                    <h5 class="fw-bold mb-4"><i class="fas fa-filter me-2 text-primary"></i>Bộ Lọc Tìm Kiếm</h5>
                    
                    <div class="mb-4">
                        <div class="filter-title">Theo Danh Mục</div>
                        <?php if(isset($categories)): foreach($categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="category_id" value="<?php echo $cat['id']; ?>" id="cat_<?php echo $cat['id']; ?>" 
                                    <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'checked' : ''; ?>
                                    onchange="document.getElementById('filterForm').submit();">
                                <label class="form-check-label text-muted" style="cursor:pointer;" for="cat_<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </label>
                            </div>
                        <?php endforeach; endif; ?>
                        <div class="form-check mt-2 border-top pt-2">
                            <input class="form-check-input" type="radio" name="category_id" value="" id="cat_all" 
                                <?php echo (empty($_GET['category_id'])) ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();">
                            <label class="form-check-label fw-bold text-dark" style="cursor:pointer;" for="cat_all">Tất cả danh mục</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="filter-title">Nền tảng</div>
                        <?php 
                        $plats = ['Tiki', 'Shopee', 'Lazada'];
                        foreach($plats as $plat): 
                        ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="platform_filter" value="<?php echo $plat; ?>" id="plat_<?php echo $plat; ?>"
                                    <?php echo (isset($_GET['platform_filter']) && $_GET['platform_filter'] == $plat) ? 'checked' : ''; ?>
                                    onchange="document.getElementById('filterForm').submit();">
                                <label class="form-check-label text-muted" style="cursor:pointer;" for="plat_<?php echo $plat; ?>">
                                    <?php echo $plat; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-check mt-2 border-top pt-2">
                            <input class="form-check-input" type="radio" name="platform_filter" value="" id="plat_all" 
                                <?php echo (empty($_GET['platform_filter'])) ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();">
                            <label class="form-check-label fw-bold text-dark" style="cursor:pointer;" for="plat_all">Tất cả sàn</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="filter-title">Khoảng Giá (VNĐ)</div>
                        <div class="d-flex align-items-center mb-3">
                            <input type="number" name="min_price" class="form-control form-control-sm text-center" placeholder="TỪ" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                            <span class="mx-2 text-muted">-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm text-center" placeholder="ĐẾN" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-bold">Áp Dụng Khoảng Giá</button>
                    </div>
                    
                    <a href="index.php?role=user&controller=product&action=search&keyword=<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>" class="btn btn-light btn-sm w-100 text-danger border">
                        <i class="fas fa-trash-alt me-2"></i>Xóa toàn bộ lọc
                    </a>
                </div>
            </div>

            <div class="col-lg-9 col-md-8">
                
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4">
                    <span class="text-muted fw-bold">Ưu tiên xem:</span>
                    <select name="sort_by" class="form-select w-auto border-0 bg-light fw-bold" onchange="document.getElementById('filterForm').submit();">
                        <option value="newest" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'newest') ? 'selected' : ''; ?>>Hàng Mới Nhất</option>
                        <option value="price_asc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_asc') ? 'selected' : ''; ?>>Giá Thấp Đến Cao</option>
                        <option value="price_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_desc') ? 'selected' : ''; ?>>Giá Cao Đến Thấp</option>
                    </select>
                </div>

                <?php if(empty($products)): ?>
                    <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                        <i class="fas fa-search-minus fa-5x text-muted opacity-25 mb-3"></i>
                        <h4 class="fw-bold text-dark">Rất tiếc, không tìm thấy kết quả!</h4>
                        <p class="text-muted">Thử thay đổi từ khóa hoặc xóa bớt các điều kiện lọc bên trái xem sao nhé.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach($products as $p): ?>
                        <div class="col-lg-4 col-sm-6">
                            <div class="product-card">
                                <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $p['id']; ?>" class="text-decoration-none">
                                    <div class="product-img-wrapper">
                                        <?php if(!empty($p['thumbnail_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($p['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                        <?php else: ?>
                                            <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h5>
                                </a>
                                
                                <div class="mt-auto">
                                    <div class="text-muted small mb-1">Giá tốt nhất:</div>
                                    <div class="price-min"><?php echo ($p['min_price'] > 0) ? number_format($p['min_price']).' đ' : 'Đang cập nhật'; ?></div>
                                    
                                    <div class="platform-prices mt-2 pt-2 border-top">
                                        <div class="text-center <?php echo ($p['tiki_price'] > 0) ? '' : 'opacity-25'; ?>">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png" class="plat-icon mb-1">
                                        </div>
                                        <div class="text-center <?php echo ($p['shopee_price'] > 0) ? '' : 'opacity-25'; ?>">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg" class="plat-icon mb-1">
                                        </div>
                                        <div class="text-center <?php echo ($p['lazada_price'] > 0) ? '' : 'opacity-25'; ?>">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png" class="plat-icon mb-1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
