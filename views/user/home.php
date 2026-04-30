<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Hệ Thống So Sánh Giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary-color: #ff4a00; --bg-gray: #f5f5fa; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* NAVBAR & MEGA MENU */
        .navbar-main { background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .dropdown-mega:hover .dropdown-menu { display: flex; flex-wrap: wrap; }
        .dropdown-mega .dropdown-menu { display: none; position: absolute; width: 600px; padding: 20px; border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: 0; }
        .category-item { width: 33.33%; padding: 10px; border-radius: 8px; transition: 0.2s; color: #333; text-decoration: none; display: flex; align-items: center; }
        .category-item:hover { background-color: #fff4f0; color: var(--primary-color); }
        .category-item i { font-size: 1.2rem; margin-right: 10px; width: 25px; text-align: center; color: var(--primary-color); }

        /* Bỏ mũi tên mặc định của dropdown bootstrap */
        .dropdown-toggle::after { display: none; }

        /* SEARCH BAR THÔNG MINH */
        .search-wrapper { max-width: 800px; margin: -30px auto 40px; position: relative; z-index: 10; }
        .search-box { background: white; padding: 10px; border-radius: 50px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: flex; align-items: center; }
        .search-input { border: none; box-shadow: none !important; padding-left: 20px; font-size: 1.1rem; }
        .search-btn { background: var(--primary-color); color: white; border-radius: 50px; padding: 10px 30px; font-weight: bold; border: none; transition: 0.2s; }
        .search-btn:hover { background: #e04000; transform: translateY(-2px); }
        
        /* KẾT QUẢ TÌM KIẾM AJAX (AJAX Suggestion Dropdown) */
        .search-suggestions { display: none; position: absolute; top: 110%; left: 0; right: 0; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); padding: 15px 0; z-index: 1000; }

        /* PRODUCT CARD UI */
        .section-title { font-weight: 800; color: #1a1a1a; margin-bottom: 25px; text-transform: uppercase; font-size: 1.4rem; }
        .product-card { background: white; border-radius: 12px; padding: 15px; border: 1px solid #f0f0f0; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); height: 100%; display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: transparent; }
        .product-img-wrapper { height: 180px; background: #f8f9fa; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; position: relative; }
        .product-img-wrapper img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
        .product-title { font-size: 0.95rem; font-weight: 600; color: #333; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .badge-cheapest { position: absolute; top: 10px; left: -30px; background: #ff424e; color: white; padding: 5px 30px; font-size: 0.75rem; font-weight: bold; transform: rotate(-45deg); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .price-box { margin-top: auto; }
        .price-min { font-size: 1.3rem; font-weight: bold; color: #ff424e; }
        
        /* CỘT GIÁ SO SÁNH */
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
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown dropdown-mega me-3">
                    <a class="nav-link fw-bold dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-bars me-2"></i>Danh Mục
                    </a>
                    <div class="dropdown-menu shadow">
                        <div class="w-100 mb-3 border-bottom pb-2 fw-bold text-muted"><i class="fas fa-th-large me-2"></i>Tất cả ngành hàng</div>
                        <?php if(isset($categories)): foreach($categories as $cat): ?>
                            <a href="index.php?role=user&controller=product&action=search&category_id=<?php echo $cat['id']; ?>" class="category-item">
                                <i class="<?php echo $cat['icon']; ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; endif; ?>
                    </div>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                    
                    <div class="me-3 d-none d-md-block border-end pe-3">
                        <span class="text-muted small">Xin chào,</span>
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                            <i class="fas fa-user-circle me-1 text-primary"></i>
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Thành viên'); ?>
                        </div>
                    </div>

                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="index.php?role=admin&controller=dashboard&action=index" class="btn btn-warning rounded-pill px-3 fw-bold me-3 shadow-sm btn-sm">
                            <i class="fas fa-user-shield me-1"></i> Quản trị
                        </a>
                    <?php endif; ?>

                    <div class="nav-item dropdown me-3" style="list-style: none;">
                        <a href="#" class="btn btn-light position-relative rounded-circle dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="notifDropdown" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-bell"></i>
                            <?php if(isset($unread_count) && $unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem;">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg p-0" aria-labelledby="notifDropdown" style="width: 350px; border: none; border-radius: 12px; overflow: hidden; top: 120%;">
                            <li class="bg-primary text-white px-4 py-3 fw-bold d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-bell me-2"></i>Thông báo của bạn</span>
                                <a href="index.php?role=user&controller=product&action=myAlerts" class="text-white small text-decoration-none" style="opacity: 0.8;">Quản lý</a>
                            </li>
                            
                            <div class="notif-list" style="max-height: 320px; overflow-y: auto;">
                                <?php if(isset($notifications) && !empty($notifications)): ?>
                                    <?php foreach($notifications as $notif): ?>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center py-3 border-bottom <?php echo ($notif['is_read'] == 0) ? 'bg-light' : ''; ?>" 
                                               href="index.php?role=user&controller=product&action=readNotification&notif_id=<?php echo $notif['id']; ?>&product_id=<?php echo $notif['product_id']; ?>" style="white-space: normal;">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-arrow-down"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark small" style="line-height: 1.4;">
                                                        Giá sản phẩm đã chạm mức kỳ vọng của bạn!
                                                    </div>
                                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                                        <i class="far fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="px-4 py-5 text-center text-muted">
                                        <i class="far fa-bell-slash fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0 small">Bạn chưa có thông báo nào.</p>
                                    </li>
                                <?php endif; ?>
                            </div>
                            
                            <li class="text-center py-2 bg-light border-top">
                                <a href="index.php?role=user&controller=product&action=myAlerts" class="small fw-bold text-decoration-none text-primary">Xem tất cả danh sách theo dõi</a>
                            </li>
                        </ul>
                    </div>

                    <a href="index.php?role=user&controller=auth&action=logout" class="btn btn-outline-danger rounded-pill px-4 fw-bold btn-sm">Đăng xuất</a>
                
                <?php else: ?>
                    <a href="index.php?role=user&controller=auth&action=login" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 60px 0 80px;">
    <div class="container text-center text-white">
        <h1 class="fw-bold mb-3">Tìm Kiếm Giá Trị Đích Thực</h1>
        <p class="fs-5 mb-0 opacity-75">So sánh giá hàng triệu sản phẩm từ Tiki, Shopee, Lazada trong tích tắc.</p>
    </div>
</div>

<div class="container search-wrapper">
    <form action="index.php" method="GET" class="search-box">
        <input type="hidden" name="role" value="user">
        <input type="hidden" name="controller" value="product">
        <input type="hidden" name="action" value="search">
        
        <select name="platform_filter" class="form-select border-0 px-3 fw-bold text-muted" style="max-width: 150px; background: transparent; border-right: 2px solid #eee !important; border-radius: 0;">
            <option value="">Tất cả Sàn</option>
            <option value="Shopee">Shopee</option>
            <option value="Lazada">Lazada</option>
            <option value="Tiki">Tiki</option>
        </select>

        <input type="text" name="keyword" id="searchInput" class="form-control search-input" placeholder="Bạn muốn tìm giá rẻ cho món đồ nào?" autocomplete="off">
        
        <button type="submit" class="search-btn"><i class="fas fa-search me-2"></i>Tìm kiếm</button>

        <div id="searchSuggestions" class="search-suggestions">
            <div class="text-center text-muted small py-3"><i class="fas fa-spinner fa-spin me-2"></i>Đang tìm gợi ý...</div>
        </div>
    </form>
</div>

<div class="container mb-5">
    
    <?php if(!empty($top_deals)): ?>
    <h3 class="section-title"><i class="fas fa-fire text-danger me-2"></i>Deal Tốt Nhất Hệ Thống</h3>
    <div class="row g-4 mb-5">
        <?php foreach($top_deals as $p): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="product-card">
                <?php if($p['total_active_links'] >= 2): ?>
                    <div class="badge-cheapest text-center w-100">Cạnh Tranh Nhất</div>
                <?php endif; ?>
                
                <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $p['id']; ?>" class="text-decoration-none">
                    <div class="product-img-wrapper">
                        <?php if(!empty($p['thumbnail_url'])): ?>
                            <img src="<?php echo htmlspecialchars($p['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-box-open fa-4x text-muted opacity-25"></i>
                        <?php endif; ?>
                    </div>
                    <h5 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h5>
                </a>
                
                <div class="price-box">
                    <div class="text-muted small mb-1">Giá thấp nhất tìm thấy:</div>
                    <div class="price-min"><?php echo ($p['min_price'] > 0) ? number_format($p['min_price']).' đ' : 'Chưa có giá'; ?></div>
                    
                    <div class="platform-prices mt-2 pt-2 border-top">
                        <div class="text-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png" class="plat-icon mb-1">
                            <div class="small fw-bold text-muted">Có link</div>
                        </div>
                        <div class="text-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg" class="plat-icon mb-1">
                            <div class="small fw-bold text-muted">Có link</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if(!empty($new_products)): ?>
    <h3 class="section-title"><i class="fas fa-star text-warning me-2"></i>Sản Phẩm Mới</h3>
    <div class="row g-4">
        <?php foreach($new_products as $p): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="product-card">
                <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $p['id']; ?>" class="text-decoration-none">
                    <div class="product-img-wrapper">
                        <?php if(!empty($p['thumbnail_url'])): ?>
                            <img src="<?php echo htmlspecialchars($p['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                        <?php endif; ?>
                    </div>
                    <h5 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h5>
                </a>
                
                <div class="price-box">
                    <div class="text-muted small mb-1">Cập nhật giá từ <?php echo $p['total_active_links']; ?> sàn</div>
                    <div class="price-min text-dark"><?php echo ($p['min_price'] > 0) ? number_format($p['min_price']).' đ' : 'Chờ Bot quét'; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let debounceTimer;
const searchInput = document.getElementById('searchInput');
const suggestionsBox = document.getElementById('searchSuggestions');

searchInput.addEventListener('input', function() {
    const keyword = this.value.trim();
    
    clearTimeout(debounceTimer);
    if (keyword.length < 2) {
        suggestionsBox.style.display = 'none';
        return;
    }

    // Debounce 300ms: Chỉ gửi request khi người dùng ngừng gõ 0.3 giây
    debounceTimer = setTimeout(() => {
        fetch(`index.php?role=user&controller=product&action=suggest&keyword=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

                let html = '';
                data.forEach(item => {
                    // Highlight từ khóa trong kết quả
                    const regex = new RegExp(`(${keyword})`, 'gi');
                    const highlightedName = item.name.replace(regex, '<b class="text-primary">$1</b>');
                    
                    const icon = item.type === 'product' ? 'fa-tag' : 'fa-th-large';
                    const link = item.type === 'product' 
                        ? `index.php?role=user&controller=product&action=detail&id=${item.id}`
                        : `index.php?role=user&controller=product&action=search&category_id=${item.id}`;

                    html += `
                        <a href="${link}" class="d-flex align-items-center px-4 py-2 text-decoration-none text-dark suggestion-item">
                            <i class="fas ${icon} me-3 text-muted" style="width: 20px;"></i>
                            <div>
                                <div class="small fw-bold">${highlightedName}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">${item.type === 'product' ? 'Sản phẩm' : 'Danh mục'}</div>
                            </div>
                        </a>
                    `;
                });
                suggestionsBox.innerHTML = html;
                suggestionsBox.style.display = 'block';
            });
    }, 300);
});

// Đóng gợi ý khi click ra ngoài
document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
        suggestionsBox.style.display = 'none';
    }
});
</script>

<style>
.suggestion-item:hover { background-color: #f8f9fa; }
.search-suggestions { max-height: 400px; overflow-y: auto; }
</style>
</body>
</html>
