<?php
function e_search($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function price_search($price) {
    return (!empty($price) && (int) $price > 0) ? number_format((int) $price) . ' đ' : 'Đang cập nhật';
}

$keywordValue = trim((string) ($_GET['keyword'] ?? $keyword ?? ''));
$selectedCategory = (int) ($_GET['category_id'] ?? 0);
$selectedPlatform = $_GET['platform_filter'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - SmartPrice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --ink: #111827;
            --muted: #667085;
            --line: #e6e8ef;
            --page: #f5f7fb;
            --brand: #f7c600;
            --accent: #d92d20;
            --blue: #0b5fff;
        }

        body { background: var(--page); color: var(--ink); font-family: "Segoe UI", Arial, sans-serif; }
        a { color: inherit; }

        .header {
            background: #111827;
            box-shadow: 0 8px 20px rgba(16,24,40,0.18);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .header-inner {
            min-height: 74px;
            display: grid;
            grid-template-columns: auto minmax(320px, 1fr) auto;
            align-items: center;
            gap: 18px;
        }

        .brand {
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 1.3rem;
        }

        .brand span:first-child {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--brand);
            color: #111827;
            display: grid;
            place-items: center;
        }

        .search-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 52px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .search-form:focus-within { border-color: var(--brand); }
        .search-form input {
            height: 48px;
            border: 0;
            outline: 0;
            padding: 0 16px;
        }

        .search-form button {
            border: 0;
            background: var(--brand);
            color: #111827;
            font-size: 1.05rem;
        }

        .header-action {
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 14px;
            border-radius: 8px;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.08);
            text-decoration: none;
            font-weight: 800;
            white-space: nowrap;
        }

        .header-action:hover { color: #fff; background: rgba(255,255,255,0.14); }

        .result-hero {
            background: #fff;
            border-bottom: 1px solid var(--line);
        }

        .result-hero-inner {
            padding: 24px 0;
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 18px;
        }

        .eyebrow {
            color: var(--accent);
            text-transform: uppercase;
            font-size: 0.78rem;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .result-title {
            margin: 0;
            font-weight: 900;
            font-size: 1.65rem;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 18px;
            padding: 24px 0 48px;
        }

        .filter-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            position: sticky;
            top: 94px;
            align-self: start;
        }

        .filter-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 18px;
        }

        .filter-heading h2 {
            font-size: 1rem;
            font-weight: 900;
            margin: 0;
        }

        .filter-block { border-top: 1px solid var(--line); padding-top: 16px; margin-top: 16px; }
        .filter-label {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 34px;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.92rem;
        }

        .filter-option input { accent-color: var(--blue); }
        .price-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .price-inputs input {
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--line);
            padding: 0 10px;
            min-width: 0;
        }

        .apply-btn, .clear-btn {
            height: 40px;
            border-radius: 8px;
            font-weight: 900;
            width: 100%;
            border: 0;
        }

        .apply-btn { background: #111827; color: #fff; }
        .clear-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #fff5f5;
            color: var(--accent);
            border: 1px solid #fee4e2;
        }

        .toolbar {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .toolbar select {
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--line);
            padding: 0 12px;
            font-weight: 800;
            background: #fff;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .product-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            min-height: 360px;
            display: flex;
            flex-direction: column;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .product-card:hover {
            transform: translateY(-3px);
            border-color: #d0d5dd;
            box-shadow: 0 14px 30px rgba(16,24,40,0.11);
        }

        .product-link { text-decoration: none; }
        .product-media {
            height: 172px;
            border-radius: 8px;
            background: #f8fafc;
            display: grid;
            place-items: center;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
            transition: transform 0.18s ease;
        }

        .product-card:hover .product-media img { transform: scale(1.035); }
        .product-media i { font-size: 3rem; color: #cbd5e1; }
        .product-meta {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-name {
            min-height: 44px;
            margin: 0;
            font-size: 0.96rem;
            line-height: 1.35;
            font-weight: 780;
            color: var(--ink);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-bottom { margin-top: auto; padding-top: 12px; }
        .price-label { color: var(--muted); font-size: 0.78rem; font-weight: 800; }
        .product-price { color: var(--accent); font-size: 1.18rem; font-weight: 900; }
        .platform-row {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            display: flex;
            gap: 8px;
        }

        .platform-pill {
            width: 34px;
            height: 28px;
            border: 1px solid var(--line);
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #fff;
        }

        .platform-pill.is-muted { opacity: 0.28; }
        .platform-pill img { width: 22px; height: 18px; object-fit: contain; }

        .empty-state {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 56px 22px;
            text-align: center;
        }

        .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 16px; }

        @media (max-width: 1100px) {
            .header-inner { grid-template-columns: 1fr; padding: 14px 0; }
            .brand { justify-content: center; }
            .layout { grid-template-columns: 1fr; }
            .filter-panel { position: static; }
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .result-hero-inner, .toolbar { flex-direction: column; align-items: stretch; }
            .product-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container header-inner">
        <a href="index.php" class="brand">
            <span><i class="fas fa-tags"></i></span>
            <strong>SmartPrice</strong>
        </a>

        <form action="index.php" method="GET" class="search-form">
            <input type="hidden" name="role" value="user">
            <input type="hidden" name="controller" value="product">
            <input type="hidden" name="action" value="search">
            <input type="text" name="keyword" value="<?php echo e_search($keywordValue); ?>" placeholder="Tìm sản phẩm, danh mục, thương hiệu...">
            <button type="submit" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
        </form>

        <a href="index.php" class="header-action"><i class="fas fa-home"></i>Trang chủ</a>
    </div>
</header>

<section class="result-hero">
    <div class="container result-hero-inner">
        <div>
            <div class="eyebrow">Kết quả tìm kiếm</div>
            <h1 class="result-title">
                <?php echo $keywordValue !== '' ? '"' . e_search($keywordValue) . '"' : 'Tất cả sản phẩm'; ?>
            </h1>
            <div class="text-muted fw-bold mt-1"><?php echo count($products); ?> sản phẩm phù hợp</div>
        </div>
        <a href="index.php?role=user&controller=product&action=search" class="clear-btn px-3" style="width:auto;">Xem tất cả</a>
    </div>
</section>

<main class="container">
    <form action="index.php" method="GET" id="filterForm" class="layout">
        <input type="hidden" name="role" value="user">
        <input type="hidden" name="controller" value="product">
        <input type="hidden" name="action" value="search">
        <input type="hidden" name="keyword" value="<?php echo e_search($keywordValue); ?>">

        <aside class="filter-panel">
            <div class="filter-heading">
                <h2><i class="fas fa-sliders me-2 text-primary"></i>Bộ lọc</h2>
            </div>

            <div class="filter-block mt-0 pt-0 border-0">
                <div class="filter-label">Danh mục</div>
                <label class="filter-option">
                    <input type="radio" name="category_id" value="" <?php echo $selectedCategory === 0 ? 'checked' : ''; ?> onchange="this.form.submit();">
                    Tất cả danh mục
                </label>
                <?php if(isset($categories)): foreach($categories as $cat): ?>
                    <label class="filter-option">
                        <input type="radio" name="category_id" value="<?php echo (int) $cat['id']; ?>" <?php echo $selectedCategory === (int) $cat['id'] ? 'checked' : ''; ?> onchange="this.form.submit();">
                        <?php echo e_search($cat['name']); ?>
                    </label>
                <?php endforeach; endif; ?>
            </div>

            <div class="filter-block">
                <div class="filter-label">Sàn thương mại</div>
                <?php foreach(['' => 'Tất cả sàn', 'Tiki' => 'Tiki', 'Shopee' => 'Shopee', 'Lazada' => 'Lazada'] as $value => $label): ?>
                    <label class="filter-option">
                        <input type="radio" name="platform_filter" value="<?php echo e_search($value); ?>" <?php echo $selectedPlatform === $value ? 'checked' : ''; ?> onchange="this.form.submit();">
                        <?php echo e_search($label); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="filter-block">
                <div class="filter-label">Khoảng giá</div>
                <div class="price-inputs mb-2">
                    <input type="number" name="min_price" placeholder="Từ" value="<?php echo e_search($minPrice); ?>">
                    <input type="number" name="max_price" placeholder="Đến" value="<?php echo e_search($maxPrice); ?>">
                </div>
                <button type="submit" class="apply-btn">Áp dụng</button>
            </div>

            <div class="filter-block">
                <a href="index.php?role=user&controller=product&action=search&keyword=<?php echo urlencode($keywordValue); ?>" class="clear-btn">
                    <i class="fas fa-rotate-left me-2"></i>Xóa bộ lọc
                </a>
            </div>
        </aside>

        <section>
            <div class="toolbar">
                <div class="fw-bold text-muted">Sắp xếp sản phẩm</div>
                <select name="sort_by" onchange="this.form.submit();">
                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                    <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                </select>
            </div>

            <?php if(empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-magnifying-glass-minus"></i>
                    <h2 class="h4 fw-bold">Không tìm thấy sản phẩm phù hợp</h2>
                    <p class="text-muted mb-0">Hãy thử từ khóa ngắn hơn hoặc bỏ bớt điều kiện lọc.</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach($products as $p): ?>
                        <article class="product-card">
                            <a href="index.php?role=user&controller=product&action=detail&id=<?php echo (int) $p['id']; ?>" class="product-link">
                                <div class="product-media">
                                    <?php if(!empty($p['thumbnail_url'])): ?>
                                        <img src="<?php echo e_search($p['thumbnail_url']); ?>" alt="<?php echo e_search($p['name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-box-open"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="product-meta"><?php echo e_search($p['category_name'] ?? 'Sản phẩm'); ?></div>
                                <h2 class="product-name"><?php echo e_search($p['name']); ?></h2>
                            </a>

                            <div class="product-bottom">
                                <div class="price-label">Giá tốt nhất</div>
                                <div class="product-price"><?php echo price_search($p['min_price'] ?? 0); ?></div>

                                <div class="platform-row">
                                    <span class="platform-pill <?php echo !empty($p['tiki_price']) ? '' : 'is-muted'; ?>">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png" alt="Tiki">
                                    </span>
                                    <span class="platform-pill <?php echo !empty($p['shopee_price']) ? '' : 'is-muted'; ?>">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg" alt="Shopee">
                                    </span>
                                    <span class="platform-pill <?php echo !empty($p['lazada_price']) ? '' : 'is-muted'; ?>">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png" alt="Lazada">
                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
