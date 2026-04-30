<?php
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function price_label($price) {
    return (!empty($price) && (int) $price > 0) ? number_format((int) $price) . ' đ' : 'Đang cập nhật';
}

function product_card($p, $mode = 'default') {
    $id = (int) ($p['id'] ?? 0);
    $name = $p['name'] ?? 'Sản phẩm';
    $thumb = trim((string) ($p['thumbnail_url'] ?? ''));
    $minPrice = $p['min_price'] ?? 0;
    $linkCount = (int) ($p['total_active_links'] ?? 0);
    $categoryName = $p['category_name'] ?? 'Sản phẩm';
    $badge = $mode === 'deal' && $linkCount >= 2 ? 'Giá cạnh tranh' : ($mode === 'new' ? 'Mới cập nhật' : '');
    ?>
    <article class="product-card">
        <?php if ($badge): ?>
            <div class="product-badge"><?php echo e($badge); ?></div>
        <?php endif; ?>

        <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $id; ?>" class="product-link">
            <div class="product-media">
                <?php if ($thumb !== ''): ?>
                    <img src="<?php echo e($thumb); ?>" alt="<?php echo e($name); ?>">
                <?php else: ?>
                    <i class="fas fa-box-open"></i>
                <?php endif; ?>
            </div>

            <div class="product-meta"><?php echo e($categoryName); ?></div>
            <h3 class="product-name"><?php echo e($name); ?></h3>
        </a>

        <div class="product-bottom">
            <div class="price-label">Giá tốt nhất</div>
            <div class="product-price"><?php echo price_label($minPrice); ?></div>
            <div class="product-row">
                <span><i class="fas fa-link"></i><?php echo $linkCount; ?> sàn</span>
                <span><i class="fas fa-chart-line"></i>Theo dõi giá</span>
            </div>
        </div>
    </article>
    <?php
}

$userLabel = $_SESSION['user_email'] ?? 'Thành viên';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPrice - So sánh giá đa sàn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --ink: #1f2937;
            --muted: #667085;
            --line: #e6e8ef;
            --surface: #ffffff;
            --page: #f5f7fb;
            --brand: #f7c600;
            --brand-dark: #111827;
            --accent: #d92d20;
            --blue: #0b5fff;
            --green: #0f8a5f;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        a { color: inherit; }

        .topbar {
            background: #101828;
            color: #d0d5dd;
            font-size: 0.82rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .topbar-inner {
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .topbar-links {
            display: flex;
            gap: 18px;
            align-items: center;
            white-space: nowrap;
        }

        .topbar-links span, .topbar-links a {
            color: #d0d5dd;
            text-decoration: none;
        }

        .main-header {
            background: #111827;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 8px 20px rgba(16,24,40,0.18);
        }

        .header-grid {
            min-height: 74px;
            display: grid;
            grid-template-columns: auto minmax(360px, 1fr) auto;
            align-items: center;
            gap: 18px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: 0;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--brand);
            color: #101828;
        }

        .search-panel {
            position: relative;
        }

        .search-form {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr) 52px;
            align-items: center;
            background: #ffffff;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
        }

        .search-form:focus-within {
            border-color: var(--brand);
        }

        .platform-select {
            height: 48px;
            border: 0;
            border-right: 1px solid var(--line);
            padding: 0 14px;
            color: var(--muted);
            font-weight: 700;
            outline: none;
            background: #fff;
        }

        .search-input {
            height: 48px;
            border: 0;
            padding: 0 16px;
            outline: none;
            min-width: 0;
        }

        .search-btn {
            width: 52px;
            height: 48px;
            border: 0;
            background: var(--brand);
            color: #101828;
            font-size: 1.05rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-btn {
            min-height: 42px;
            border-radius: 8px;
            padding: 0 14px;
            border: 1px solid rgba(255,255,255,0.18);
            color: #ffffff;
            background: rgba(255,255,255,0.08);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }

        .action-btn:hover { color: #ffffff; background: rgba(255,255,255,0.14); }
        .action-btn.primary { background: var(--brand); color: #101828; border-color: var(--brand); }
        .action-btn.primary:hover { color: #101828; background: #ffe066; }
        .icon-action { width: 42px; padding: 0; justify-content: center; position: relative; }
        .notification-dot {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 20px;
            height: 20px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            font-size: 0.72rem;
            border: 2px solid #111827;
        }

        .notif-menu {
            width: min(360px, calc(100vw - 24px));
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(16,24,40,0.22);
        }

        .notif-head {
            background: #111827;
            color: #fff;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 800;
        }

        .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            text-decoration: none;
            border-bottom: 1px solid var(--line);
            white-space: normal;
        }

        .notif-item:hover { background: #f9fafb; }
        .notif-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #ecfdf3;
            color: var(--green);
            flex: 0 0 auto;
        }

        .category-bar {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
        }

        .category-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .category-strip::-webkit-scrollbar { display: none; }
        .cat-chip {
            height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 0 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            color: var(--ink);
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }

        .cat-chip:hover {
            color: #101828;
            border-color: var(--brand);
            background: #fffbeb;
        }

        .cat-chip i { color: var(--blue); width: 18px; text-align: center; }

        .market-band {
            background: #ffffff;
            border-bottom: 1px solid var(--line);
        }

        .market-layout {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            gap: 16px;
            padding: 22px 0;
        }

        .deal-stage {
            min-height: 230px;
            border-radius: 8px;
            background:
                linear-gradient(135deg, rgba(247,198,0,0.95), rgba(255,237,120,0.9)),
                linear-gradient(135deg, #111827, #344054);
            padding: 28px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            overflow: hidden;
        }

        .deal-stage h1 {
            font-size: clamp(1.65rem, 3vw, 2.55rem);
            line-height: 1.08;
            font-weight: 900;
            margin: 0 0 10px;
            color: #111827;
            letter-spacing: 0;
        }

        .deal-stage p {
            color: #344054;
            margin: 0;
            max-width: 560px;
            font-weight: 600;
        }

        .deal-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .deal-button {
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 800;
            background: #111827;
            color: #fff;
        }

        .deal-button:hover { color: #fff; background: #1f2937; }
        .deal-button.light { background: rgba(255,255,255,0.72); color: #111827; }
        .deal-button.light:hover { color: #111827; background: #fff; }

        .deal-visual {
            width: 170px;
            height: 170px;
            border-radius: 8px;
            background: rgba(255,255,255,0.34);
            display: grid;
            place-items: center;
            color: #111827;
            font-size: 4.4rem;
        }

        .side-panel {
            display: grid;
            gap: 16px;
        }

        .side-tile {
            border-radius: 8px;
            min-height: 107px;
            padding: 18px;
            background: #f2f4f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            text-decoration: none;
        }

        .side-tile:nth-child(2) { background: #eef4ff; }
        .side-tile strong {
            display: block;
            color: #111827;
            font-size: 1.02rem;
            margin-bottom: 4px;
        }

        .side-tile span {
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 600;
        }

        .side-tile i {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #fff;
            color: var(--blue);
            flex: 0 0 auto;
        }

        .section-wrap { padding: 28px 0 44px; }
        .section-header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .section-kicker {
            color: var(--accent);
            font-weight: 900;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0;
            margin-bottom: 4px;
        }

        .section-title {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 900;
            color: #111827;
        }

        .section-link {
            color: var(--blue);
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .product-card {
            position: relative;
            background: var(--surface);
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

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 2;
            height: 26px;
            display: inline-flex;
            align-items: center;
            padding: 0 9px;
            border-radius: 6px;
            background: var(--accent);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 900;
        }

        .product-link {
            text-decoration: none;
            display: block;
        }

        .product-media {
            height: 172px;
            border-radius: 8px;
            background: #f8fafc;
            display: grid;
            place-items: center;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .product-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
            transition: transform 0.18s ease;
        }

        .product-card:hover .product-media img { transform: scale(1.035); }
        .product-media i {
            font-size: 3rem;
            color: #cbd5e1;
        }

        .product-meta {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-name {
            min-height: 44px;
            margin: 0;
            color: #111827;
            font-size: 0.96rem;
            line-height: 1.35;
            font-weight: 750;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-bottom {
            margin-top: auto;
            padding-top: 12px;
        }

        .price-label {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .product-price {
            color: var(--accent);
            font-size: 1.18rem;
            font-weight: 900;
            line-height: 1.25;
            margin-top: 2px;
        }

        .product-row {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 8px;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .product-row span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-width: 0;
        }

        .product-row i { color: var(--blue); }

        .suggestions {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 16px 34px rgba(16,24,40,0.16);
            max-height: 380px;
            overflow-y: auto;
            z-index: 1030;
        }

        .suggestion-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: var(--ink);
            text-decoration: none;
            border-bottom: 1px solid #f2f4f7;
        }

        .suggestion-item:hover { background: #f9fafb; }
        .suggestion-item i {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #eef4ff;
            color: var(--blue);
            flex: 0 0 auto;
        }

        .empty-state {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 32px;
            color: var(--muted);
            text-align: center;
            font-weight: 700;
        }

        @media (max-width: 1100px) {
            .header-grid { grid-template-columns: 1fr; padding: 14px 0; }
            .brand { justify-content: center; }
            .header-actions { justify-content: center; flex-wrap: wrap; }
            .market-layout { grid-template-columns: 1fr; }
            .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 768px) {
            .topbar { display: none; }
            .header-grid { gap: 12px; }
            .search-form { grid-template-columns: 1fr 48px; }
            .platform-select { display: none; }
            .deal-stage { grid-template-columns: 1fr; padding: 22px; min-height: auto; }
            .deal-visual { display: none; }
            .side-panel { grid-template-columns: 1fr; }
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .section-header { align-items: start; flex-direction: column; }
            .action-btn span { display: none; }
            .action-btn.primary span { display: inline; }
        }

        @media (max-width: 480px) {
            .product-grid { grid-template-columns: 1fr; }
            .product-card { min-height: auto; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-links">
            <span><i class="fas fa-shield-alt me-1"></i> Cập nhật giá đa sàn</span>
            <span><i class="fas fa-bell me-1"></i> Cảnh báo giảm giá</span>
        </div>
        <div class="topbar-links">
            <a href="index.php?role=user&controller=product&action=myAlerts">Danh sách theo dõi</a>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="index.php?role=admin&controller=dashboard&action=index">Quản trị</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="main-header">
    <div class="container header-grid">
        <a class="brand" href="index.php" aria-label="SmartPrice">
            <span class="brand-mark"><i class="fas fa-tags"></i></span>
            <span>SmartPrice</span>
        </a>

        <div class="search-panel">
            <form action="index.php" method="GET" class="search-form">
                <input type="hidden" name="role" value="user">
                <input type="hidden" name="controller" value="product">
                <input type="hidden" name="action" value="search">

                <select name="platform_filter" class="platform-select" aria-label="Chọn sàn">
                    <option value="">Tất cả sàn</option>
                    <option value="Tiki">Tiki</option>
                    <option value="Shopee">Shopee</option>
                    <option value="Lazada">Lazada</option>
                </select>

                <input type="text" name="keyword" id="searchInput" class="search-input" placeholder="Tìm điện thoại, laptop, tai nghe..." autocomplete="off">
                <button type="submit" class="search-btn" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>

                <div id="searchSuggestions" class="suggestions">
                    <div class="text-center text-muted small py-3"><i class="fas fa-spinner fa-spin me-2"></i>Đang tìm...</div>
                </div>
            </form>
        </div>

        <div class="header-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?role=user&controller=product&action=myAlerts" class="action-btn">
                    <i class="fas fa-heart"></i><span>Theo dõi</span>
                </a>

                <div class="dropdown">
                    <a href="#" class="action-btn icon-action dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo">
                        <i class="fas fa-bell"></i>
                        <?php if(isset($unread_count) && $unread_count > 0): ?>
                            <span class="notification-dot"><?php echo (int) $unread_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end notif-menu">
                        <div class="notif-head">
                            <span>Thông báo</span>
                            <a href="index.php?role=user&controller=product&action=myAlerts" class="text-white text-decoration-none small">Quản lý</a>
                        </div>
                        <div class="notif-list">
                            <?php if(isset($notifications) && !empty($notifications)): ?>
                                <?php foreach($notifications as $notif): ?>
                                    <a class="notif-item <?php echo ((int) $notif['is_read'] === 0) ? 'bg-light' : ''; ?>"
                                       href="index.php?role=user&controller=product&action=readNotification&notif_id=<?php echo (int) $notif['id']; ?>&product_id=<?php echo (int) $notif['product_id']; ?>">
                                        <span class="notif-icon"><i class="fas fa-arrow-trend-down"></i></span>
                                        <span>
                                            <strong class="d-block text-dark small">Giá đã chạm mức kỳ vọng</strong>
                                            <span class="text-muted small"><?php echo e(date('d/m/Y H:i', strtotime($notif['created_at']))); ?></span>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4 small fw-bold">Chưa có thông báo mới.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="index.php?role=user&controller=auth&action=logout" class="action-btn">
                    <i class="fas fa-user"></i><span><?php echo e($userLabel); ?></span>
                </a>
            <?php else: ?>
                <a href="index.php?role=user&controller=auth&action=login" class="action-btn primary">
                    <i class="fas fa-user"></i><span>Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<nav class="category-bar">
    <div class="container category-strip">
        <a class="cat-chip" href="index.php"><i class="fas fa-fire"></i>Tất cả</a>
        <?php if(isset($categories)): foreach($categories as $cat): ?>
            <a href="index.php?role=user&controller=product&action=search&category_id=<?php echo (int) $cat['id']; ?>" class="cat-chip">
                <i class="<?php echo e($cat['icon'] ?: 'fas fa-tag'); ?>"></i><?php echo e($cat['name']); ?>
            </a>
        <?php endforeach; endif; ?>
    </div>
</nav>

<section class="market-band">
    <div class="container market-layout">
        <div class="deal-stage">
            <div>
                <h1>Săn giá tốt từ Tiki, Shopee, Lazada</h1>
                <p>Theo dõi biến động giá, so sánh nhanh giữa các sàn và mở sản phẩm đang có mức giá tốt nhất.</p>
                <div class="deal-actions">
                    <a href="#topDeals" class="deal-button"><i class="fas fa-bolt"></i>Deal nổi bật</a>
                    <a href="index.php?role=user&controller=product&action=search" class="deal-button light"><i class="fas fa-list"></i>Xem sản phẩm</a>
                </div>
            </div>
            <div class="deal-visual"><i class="fas fa-mobile-screen-button"></i></div>
        </div>

        <div class="side-panel">
            <a href="index.php?role=user&controller=product&action=search&platform_filter=Tiki" class="side-tile">
                <span><strong>Giá từ Tiki</strong><span>Ảnh và thông tin gốc rõ ràng</span></span>
                <i class="fas fa-store"></i>
            </a>
            <a href="index.php?role=user&controller=product&action=myAlerts" class="side-tile">
                <span><strong>Cảnh báo giá</strong><span>Lưu mức giá mong muốn</span></span>
                <i class="fas fa-bell"></i>
            </a>
        </div>
    </div>
</section>

<main class="section-wrap">
    <div class="container">
        <?php if(!empty($top_deals)): ?>
            <section id="topDeals" class="mb-5">
                <div class="section-header">
                    <div>
                        <div class="section-kicker">Đáng chú ý</div>
                        <h2 class="section-title">Deal tốt nhất hệ thống</h2>
                    </div>
                    <a class="section-link" href="index.php?role=user&controller=product&action=search&sort_by=price_asc">Xem thêm <i class="fas fa-angle-right ms-1"></i></a>
                </div>
                <div class="product-grid">
                    <?php foreach($top_deals as $p): ?>
                        <?php product_card($p, 'deal'); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if(!empty($trending_products)): ?>
            <section class="mb-5">
                <div class="section-header">
                    <div>
                        <div class="section-kicker">Nổi bật</div>
                        <h2 class="section-title">Sản phẩm đang được cập nhật</h2>
                    </div>
                    <a class="section-link" href="index.php?role=user&controller=product&action=search">Tất cả sản phẩm <i class="fas fa-angle-right ms-1"></i></a>
                </div>
                <div class="product-grid">
                    <?php foreach($trending_products as $p): ?>
                        <?php product_card($p); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if(!empty($new_products)): ?>
            <section>
                <div class="section-header">
                    <div>
                        <div class="section-kicker">Mới nhất</div>
                        <h2 class="section-title">Sản phẩm mới thêm</h2>
                    </div>
                    <a class="section-link" href="index.php?role=user&controller=product&action=search&sort_by=newest">Xem thêm <i class="fas fa-angle-right ms-1"></i></a>
                </div>
                <div class="product-grid">
                    <?php foreach($new_products as $p): ?>
                        <?php product_card($p, 'new'); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if(empty($top_deals) && empty($trending_products) && empty($new_products)): ?>
            <div class="empty-state">Chưa có sản phẩm để hiển thị.</div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchInput');
const suggestionsBox = document.getElementById('searchSuggestions');
let debounceTimer;

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function(char) {
        return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char]);
    });
}

searchInput.addEventListener('input', function() {
    const keyword = this.value.trim();
    clearTimeout(debounceTimer);

    if (keyword.length < 2) {
        suggestionsBox.style.display = 'none';
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch(`index.php?role=user&controller=product&action=suggest&keyword=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

                suggestionsBox.innerHTML = data.map(item => {
                    const safeName = escapeHtml(item.name || '');
                    const icon = item.type === 'product' ? 'fa-tag' : 'fa-layer-group';
                    const label = item.type === 'product' ? 'Sản phẩm' : 'Danh mục';
                    const link = item.type === 'product'
                        ? `index.php?role=user&controller=product&action=detail&id=${encodeURIComponent(item.id)}`
                        : `index.php?role=user&controller=product&action=search&category_id=${encodeURIComponent(item.id)}`;

                    return `
                        <a href="${link}" class="suggestion-item">
                            <i class="fas ${icon}"></i>
                            <span>
                                <strong class="d-block">${safeName}</strong>
                                <span class="text-muted small">${label}</span>
                            </span>
                        </a>
                    `;
                }).join('');

                suggestionsBox.style.display = 'block';
            })
            .catch(() => {
                suggestionsBox.style.display = 'none';
            });
    }, 250);
});

document.addEventListener('click', function(event) {
    if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
        suggestionsBox.style.display = 'none';
    }
});
</script>
</body>
</html>
