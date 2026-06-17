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

function insight_product_card($p) {
    $id = (int) ($p['id'] ?? 0);
    $name = $p['name'] ?? 'Sản phẩm';
    $thumb = trim((string) ($p['thumbnail_url'] ?? ''));
    $minPrice = $p['min_price'] ?? 0;
    $categoryName = $p['category_name'] ?? 'Sản phẩm';
    $recommendationReason = $p['recommendation_reason'] ?? 'Giá hiện tại đang ở vùng hợp lý để cân nhắc mua.';
    $trendLabel = $p['trend_label'] ?? 'Đang phân tích';
    ?>
    <article class="insight-card">
        <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $id; ?>" class="insight-link">
            <div class="insight-media">
                <?php if ($thumb !== ''): ?>
                    <img src="<?php echo e($thumb); ?>" alt="<?php echo e($name); ?>">
                <?php else: ?>
                    <i class="fas fa-box-open"></i>
                <?php endif; ?>
            </div>
            <div class="insight-body">
                <div class="insight-meta"><?php echo e($categoryName); ?></div>
                <h3><?php echo e($name); ?></h3>
                <div class="insight-price"><?php echo price_label($minPrice); ?></div>
                <div class="insight-reason"><i class="fas fa-lightbulb"></i><?php echo e($recommendationReason); ?></div>
                <div class="insight-trend"><i class="fas fa-arrow-trend-up"></i>Xu hướng: <?php echo e($trendLabel); ?></div>
            </div>
        </a>
    </article>
    <?php
}

function flash_sale_card($p) {
    $id = (int) ($p['id'] ?? 0);
    $name = $p['name'] ?? 'Sản phẩm';
    $thumb = trim((string) ($p['thumbnail_url'] ?? ''));
    $minPrice = (int) ($p['min_price'] ?? 0);
    $dealCurrentPrice = (int) ($p['deal_current_price'] ?? 0);
    $originalPrice = (int) ($p['original_price'] ?? 0);
    $discountPercent = (int) ($p['discount_percent'] ?? 0);
    $displayPrice = $dealCurrentPrice > 0 ? $dealCurrentPrice : $minPrice;

    if ($discountPercent <= 0 && $originalPrice > $displayPrice && $displayPrice > 0) {
        $discountPercent = (int) round((($originalPrice - $displayPrice) / $originalPrice) * 100);
    }
    ?>
    <article class="flash-card">
        <?php if ($discountPercent > 0): ?>
            <div class="flash-badge">-<?php echo $discountPercent; ?>%</div>
        <?php else: ?>
            <div class="flash-badge">Deal</div>
        <?php endif; ?>

        <a href="index.php?role=user&controller=product&action=detail&id=<?php echo $id; ?>" class="flash-link">
            <div class="flash-media">
                <?php if ($thumb !== ''): ?>
                    <img src="<?php echo e($thumb); ?>" alt="<?php echo e($name); ?>">
                <?php else: ?>
                    <i class="fas fa-box-open"></i>
                <?php endif; ?>
            </div>

            <h3><?php echo e($name); ?></h3>
            <div class="flash-price-row">
                <?php if ($originalPrice > $displayPrice && $displayPrice > 0): ?>
                    <span class="flash-original"><?php echo price_label($originalPrice); ?></span>
                <?php endif; ?>
                <strong class="flash-price"><?php echo price_label($displayPrice); ?></strong>
            </div>
            <div class="flash-note">
                <i class="fas fa-clock"></i>
                Giá tốt đang được theo dõi
            </div>
        </a>
    </article>
    <?php
}

$userLabel = trim($_SESSION['user_full_name'] ?? '') !== '' ? $_SESSION['user_full_name'] : ($_SESSION['user_email'] ?? 'Thành viên');
$homeCategories = (isset($categories) && is_array($categories)) ? array_values($categories) : [];
$quickCategoryLimit = 5;
$preferredQuickCategoryNames = [
    'Điện lạnh',
    'Tivi - Âm thanh',
    'Điện thoại - Máy tính bảng',
    'Thiết bị gia dụng',
    'Thiết bị y tế - Sức khỏe',
];
$quickCategories = [];

foreach ($preferredQuickCategoryNames as $preferredName) {
    foreach ($homeCategories as $cat) {
        if (isset($cat['name']) && trim((string) $cat['name']) === $preferredName) {
            $quickCategories[(int) $cat['id']] = $cat;
            break;
        }
    }
}

foreach ($homeCategories as $cat) {
    if (count($quickCategories) >= $quickCategoryLimit) {
        break;
    }

    $catId = (int) ($cat['id'] ?? 0);
    if ($catId > 0 && !isset($quickCategories[$catId])) {
        $quickCategories[$catId] = $cat;
    }
}

$quickCategories = array_values($quickCategories);
$categoryMegaColumns = [];

if (!empty($homeCategories)) {
    $categoryMegaColumns = array_chunk($homeCategories, max(1, (int) ceil(count($homeCategories) / 4)));
}

$bannerProducts = [];
foreach ([$top_deals ?? [], $recommended_buy_products ?? [], $trending_products ?? [], $new_products ?? []] as $productGroup) {
    if (!empty($productGroup)) {
        foreach ($productGroup as $product) {
            if (count($bannerProducts) >= 3) {
                break 2;
            }
            $bannerProducts[] = $product;
        }
    }
}

$todaySuggestionProducts = !empty($recommended_buy_products)
    ? $recommended_buy_products
    : array_slice(!empty($top_deals) ? $top_deals : (!empty($trending_products) ? $trending_products : ($new_products ?? [])), 0, 4);
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
            width: min(430px, calc(100vw - 24px));
            border: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(16,24,40,0.26);
        }

        .notif-head {
            background: linear-gradient(135deg, #111827, #263446);
            color: #fff;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .notif-title {
            display: block;
            font-weight: 950;
            font-size: 1rem;
            line-height: 1.2;
        }

        .notif-subtitle {
            display: block;
            margin-top: 3px;
            color: #d0d5dd;
            font-size: .8rem;
            font-weight: 700;
        }

        .notif-manage {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0 10px;
            border-radius: 8px;
            background: rgba(255,255,255,.12);
            color: #fff;
            text-decoration: none;
            font-size: .82rem;
            font-weight: 850;
            white-space: nowrap;
        }

        .notif-manage:hover {
            color: #fff;
            background: rgba(255,255,255,.2);
        }

        .notif-list {
            max-height: 410px;
            overflow-y: auto;
            padding: 8px;
            background: #f8fafc;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .notif-list::-webkit-scrollbar { width: 6px; }
        .notif-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

        .notif-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: 8px;
            background: #fff;
            color: #111827;
            white-space: normal;
            box-shadow: 0 6px 16px rgba(16,24,40,.04);
        }

        .notif-item + .notif-item { margin-top: 8px; }

        .notif-item.is-unread {
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .notif-item:hover {
            color: #111827;
            border-color: #0b5fff;
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(16,24,40,.1);
        }

        .notif-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #dcfce7;
            color: #15803d;
            flex: 0 0 auto;
            font-size: 1.05rem;
        }

        .notif-content {
            min-width: 0;
            flex: 1;
        }

        .notif-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 3px;
        }

        .notif-kicker {
            color: #15803d;
            font-size: .74rem;
            font-weight: 950;
            text-transform: uppercase;
        }

        .notif-unread-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #16a34a;
            flex: 0 0 auto;
        }

        .notif-product {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: #111827;
            font-size: .92rem;
            line-height: 1.35;
            font-weight: 900;
        }

        .notif-message {
            display: block;
            margin-top: 4px;
            color: #667085;
            font-size: .8rem;
            font-weight: 650;
            line-height: 1.35;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 9px;
            color: #667085;
            font-size: .78rem;
            font-weight: 750;
        }

        .notif-open {
            color: #0b5fff;
            font-weight: 900;
            white-space: nowrap;
        }

        .notif-empty {
            margin: 8px;
            padding: 34px 18px;
            border-radius: 8px;
            background: #fff;
            text-align: center;
            color: #667085;
            font-weight: 750;
        }

        .notif-empty i {
            width: 54px;
            height: 54px;
            display: inline-grid;
            place-items: center;
            border-radius: 14px;
            margin-bottom: 12px;
            background: #eef4ff;
            color: #0b5fff;
            font-size: 1.35rem;
        }

        .notif-foot {
            padding: 10px;
            background: #fff;
            border-top: 1px solid var(--line);
        }

        .notif-foot a {
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            font-weight: 900;
        }

        .category-bar {
            position: relative;
            z-index: 20;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }

        .category-nav {
            position: relative;
            display: flex;
            align-items: center;
            gap: 18px;
            min-height: 58px;
        }

        .category-menu-shell {
            position: static;
            flex: 0 0 auto;
        }

        .category-trigger {
            height: 58px;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 0 16px 0 6px;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: #f04438;
            font-size: 1rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .category-trigger:hover,
        .category-trigger:focus {
            color: #d92d20;
        }

        .category-trigger::after { display: none; }

        .category-trigger i {
            width: 28px;
            color: #ff3d12;
            font-size: 1.5rem;
            text-align: center;
        }

        .category-divider {
            width: 1px;
            height: 28px;
            background: #ff6b4a;
            flex: 0 0 auto;
        }

        .category-strip {
            min-width: 0;
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            gap: 30px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .category-strip::-webkit-scrollbar { display: none; }

        .cat-link {
            height: 58px;
            display: inline-flex;
            align-items: center;
            color: #111827;
            text-decoration: none;
            font-size: 0.96rem;
            font-weight: 780;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
        }

        .cat-link:hover,
        .cat-link:focus {
            color: #f04438;
            border-bottom-color: #f04438;
        }

        .category-mega {
            left: 0;
            right: 0;
            top: 100%;
            width: 100%;
            padding: 0;
            border: 1px solid var(--line);
            border-top: 0;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
            box-shadow: 0 24px 55px rgba(16, 24, 40, 0.14);
            transform: none !important;
        }

        .category-mega-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 58px;
            padding: 0 18px;
            background: #f7f7f8;
            border-bottom: 1px solid var(--line);
        }

        .category-mega-title {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            color: #f04438;
            font-size: 1rem;
            font-weight: 900;
        }

        .category-mega-title i {
            width: 26px;
            font-size: 1.45rem;
            text-align: center;
        }

        .category-close {
            border: 0;
            background: transparent;
            color: #98a2b3;
            font-weight: 750;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
        }

        .category-close:hover {
            color: #111827;
        }

        .category-mega-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            column-gap: 34px;
            row-gap: 10px;
            padding: 22px 24px 26px;
            background: #fff;
        }

        .category-mega-item {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            align-items: center;
            gap: 12px;
            min-height: 54px;
            padding: 7px 8px;
            border-radius: 8px;
            color: #111827;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.98rem;
        }

        .category-mega-item:hover {
            color: #f04438;
            background: #fff7ed;
        }

        .category-mega-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #f2f4f7;
            color: var(--blue);
            font-size: 1.05rem;
        }

        .category-mega-name {
            min-width: 0;
            line-height: 1.35;
        }

        .category-mega-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 18px;
            border-top: 1px solid var(--line);
            background: #f9fafb;
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .category-mega-foot a {
            color: #111827;
            text-decoration: none;
            font-weight: 800;
        }

        .promo-band {
            background: #f5f7fb;
            border-bottom: 1px solid var(--line);
        }

        .promo-layout {
            display: grid;
            grid-template-columns: minmax(0, 2.1fr) minmax(280px, 1fr);
            gap: 18px;
            padding: 22px 0;
        }

        .promo-carousel,
        .promo-slide,
        .promo-side-card {
            border-radius: 8px;
            overflow: hidden;
        }

        .promo-carousel {
            min-height: 310px;
            background: #ff4b17;
            box-shadow: 0 16px 36px rgba(16,24,40,.08);
        }

        .promo-slide {
            min-height: 310px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 230px;
            align-items: center;
            gap: 20px;
            padding: 34px 42px;
            color: #fff;
            background:
                radial-gradient(circle at 82% 20%, rgba(255,255,255,.28), transparent 26%),
                linear-gradient(135deg, #ff3d12, #ff7a00);
        }

        .promo-slide.blue {
            background:
                radial-gradient(circle at 82% 20%, rgba(255,255,255,.25), transparent 26%),
                linear-gradient(135deg, #0b5fff, #00a3ff);
        }

        .promo-slide.dark {
            background:
                radial-gradient(circle at 82% 20%, rgba(247,198,0,.24), transparent 26%),
                linear-gradient(135deg, #101828, #344054);
        }

        .promo-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            background: rgba(255,255,255,.18);
            font-weight: 900;
            font-size: .82rem;
        }

        .promo-slide h1 {
            margin: 16px 0 10px;
            max-width: 600px;
            color: #fff;
            font-size: clamp(2rem, 4vw, 3.45rem);
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: 0;
        }

        .promo-slide p {
            max-width: 560px;
            margin: 0;
            color: rgba(255,255,255,.9);
            font-weight: 700;
            line-height: 1.5;
        }

        .promo-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .promo-button {
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0 14px;
            border-radius: 8px;
            background: #fff;
            color: #111827;
            text-decoration: none;
            font-weight: 900;
        }

        .promo-button:hover { color: #111827; background: #fff7cc; }
        .promo-button.dark { background: #111827; color: #fff; }
        .promo-button.dark:hover { color: #fff; background: #1f2937; }

        .promo-products {
            display: grid;
            gap: 12px;
        }

        .promo-mini-product {
            min-height: 86px;
            display: grid;
            grid-template-columns: 70px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(6px);
        }

        .promo-mini-product img,
        .promo-mini-product .promo-mini-icon {
            width: 70px;
            height: 66px;
            object-fit: contain;
            border-radius: 8px;
            background: #fff;
        }

        .promo-mini-product .promo-mini-icon {
            display: grid;
            place-items: center;
            color: #ff4b17;
            font-size: 1.5rem;
        }

        .promo-mini-product strong {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: #fff;
            font-size: .88rem;
            line-height: 1.3;
        }

        .promo-mini-product span {
            display: block;
            margin-top: 4px;
            color: #fff7cc;
            font-weight: 950;
        }

        .promo-side {
            display: grid;
            gap: 18px;
        }

        .promo-side-card {
            min-height: 146px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px;
            color: #fff;
            text-decoration: none;
            background: linear-gradient(135deg, #d92d20, #ff7a00);
        }

        .promo-side-card:nth-child(2) {
            background: linear-gradient(135deg, #0b5fff, #00a3ff);
        }

        .promo-side-card strong {
            display: block;
            max-width: 250px;
            color: #fff;
            font-size: 1.25rem;
            line-height: 1.2;
            font-weight: 950;
        }

        .promo-side-card span {
            display: block;
            margin-top: 8px;
            color: rgba(255,255,255,.88);
            font-weight: 700;
        }

        .promo-side-card i {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.16);
            font-size: 2rem;
            flex: 0 0 auto;
        }

        .flash-sale-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 0 0 24px;
            padding: 16px 22px;
            border-radius: 8px;
            background: linear-gradient(90deg, #ff3d12, #f04438);
            color: #fff;
        }

        .flash-sale-title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.45rem;
            font-weight: 950;
        }

        .flash-countdown {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .flash-countdown span {
            min-width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #1f2937;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 950;
        }

        .flash-product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 34px;
        }

        .flash-card {
            position: relative;
            background: #fff;
            border: 1px solid #ffd3c4;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 12px 26px rgba(217,45,32,.08);
        }

        .flash-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            min-width: 52px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 9px;
            border-radius: 6px;
            background: #d92d20;
            color: #fff;
            font-size: .78rem;
            font-weight: 950;
        }

        .flash-link {
            display: block;
            height: 100%;
            color: #111827;
            text-decoration: none;
            padding: 12px;
        }

        .flash-media {
            height: 170px;
            display: grid;
            place-items: center;
            margin-bottom: 12px;
            border-radius: 8px;
            background: #fff7ed;
            overflow: hidden;
        }

        .flash-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 12px;
        }

        .flash-media i {
            color: #ffb088;
            font-size: 3rem;
        }

        .flash-card h3 {
            min-height: 42px;
            margin: 0 0 10px;
            font-size: .96rem;
            line-height: 1.35;
            font-weight: 850;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .flash-price-row {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 7px;
        }

        .flash-original {
            color: #98a2b3;
            font-size: .86rem;
            font-weight: 750;
            text-decoration: line-through;
        }

        .flash-price {
            color: #d92d20;
            font-size: 1.15rem;
            font-weight: 950;
        }

        .flash-note {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            color: #667085;
            font-size: .78rem;
            font-weight: 750;
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

        .insight-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .insight-card {
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(16,24,40,.07);
            min-height: 100%;
        }

        .insight-link {
            display: grid;
            grid-template-rows: 160px minmax(0, 1fr);
            height: 100%;
            color: var(--ink);
            text-decoration: none;
        }

        .insight-media {
            position: relative;
            background: #eff6ff;
            display: grid;
            place-items: center;
            overflow: hidden;
            padding: 12px;
        }

        .insight-media::before {
            content: "Nên mua";
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            height: 26px;
            display: inline-flex;
            align-items: center;
            padding: 0 9px;
            border-radius: 6px;
            background: #16a34a;
            color: #fff;
            font-size: .75rem;
            font-weight: 900;
        }

        .insight-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .insight-media i {
            color: #93c5fd;
            font-size: 3rem;
        }

        .insight-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .insight-meta {
            color: var(--muted);
            font-size: .78rem;
            font-weight: 800;
        }

        .insight-body h3 {
            margin: 0;
            min-height: 42px;
            font-size: .96rem;
            line-height: 1.35;
            font-weight: 850;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .insight-price {
            color: var(--accent);
            font-size: 1.15rem;
            font-weight: 950;
        }

        .insight-reason,
        .insight-trend {
            display: flex;
            align-items: flex-start;
            gap: 7px;
            color: var(--muted);
            font-size: .8rem;
            font-weight: 750;
            line-height: 1.35;
        }

        .insight-reason i {
            color: #16a34a;
            margin-top: 2px;
        }

        .insight-trend i {
            color: var(--blue);
            margin-top: 2px;
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

        .site-footer {
            background: #eef1f6;
            color: #111827;
            border-top: 1px solid #d9dee8;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.45fr 1fr 1fr 1fr;
            gap: 46px;
            padding: 38px 0 30px;
        }

        .footer-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #111827;
            font-size: 1.6rem;
            font-weight: 950;
            text-decoration: none;
        }

        .footer-brand span:first-child {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: #2aa7df;
            color: #fff;
        }

        .footer-desc {
            max-width: 360px;
            margin: 16px 0 0;
            color: #344054;
            line-height: 1.55;
            font-weight: 600;
        }

        .footer-title {
            margin: 0 0 18px;
            color: #111827;
            font-size: 1.12rem;
            font-weight: 900;
        }

        .footer-links {
            display: grid;
            gap: 11px;
        }

        .footer-links a,
        .footer-links span {
            color: #1f2937;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 650;
        }

        .footer-links a:hover {
            color: #0b5fff;
        }

        .footer-company {
            margin-top: 18px;
        }

        .footer-company strong {
            display: block;
            margin-bottom: 8px;
            color: #111827;
            font-size: 1rem;
            font-weight: 900;
        }

        .footer-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .footer-badge {
            height: 34px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0 10px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #d0d5dd;
            color: #111827;
            font-size: .82rem;
            font-weight: 900;
        }

        .footer-badge i { color: #16a34a; }

        .partner-button {
            width: fit-content;
            min-height: 54px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #e92b2b;
            color: #fff !important;
            text-decoration: none;
            font-weight: 900;
            line-height: 1.15;
        }

        .partner-button i {
            font-size: 1.6rem;
        }

        .footer-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #315ca8;
            color: #fff !important;
            font-size: 1.4rem;
            text-decoration: none;
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 0;
            border-top: 1px solid #d9dee8;
            color: #667085;
            font-size: 0.88rem;
            font-weight: 650;
        }

        @media (max-width: 1100px) {
            .header-grid { grid-template-columns: 1fr; padding: 14px 0; }
            .brand { justify-content: center; }
            .header-actions { justify-content: center; flex-wrap: wrap; }
            .promo-layout { grid-template-columns: 1fr; }
            .product-grid,
            .insight-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .footer-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 768px) {
            .topbar { display: none; }
            .header-grid { gap: 12px; }
            .search-form { grid-template-columns: 1fr 48px; }
            .platform-select { display: none; }
            .category-nav {
                align-items: stretch;
                flex-direction: column;
                gap: 0;
                padding: 8px 0;
            }
            .category-trigger {
                width: 100%;
                height: 46px;
                justify-content: flex-start;
                padding: 0;
            }
            .category-divider { display: none; }
            .category-strip {
                gap: 20px;
                overflow-x: auto;
                padding-bottom: 4px;
                scrollbar-width: none;
            }
            .category-strip::-webkit-scrollbar { display: none; }
            .cat-link {
                height: 40px;
                font-size: 0.95rem;
            }
            .category-mega {
                width: 100%;
                max-height: 72vh;
                overflow-y: auto;
                border-top: 1px solid var(--line);
            }
            .category-mega-top { padding: 0 14px; }
            .category-mega-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .promo-slide {
                min-height: 290px;
                grid-template-columns: 1fr;
                padding: 26px;
            }
            .promo-products { display: none; }
            .promo-side { grid-template-columns: 1fr; }
            .flash-sale-bar {
                align-items: flex-start;
                flex-direction: column;
            }
            .flash-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .product-grid,
            .insight-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .section-header { align-items: start; flex-direction: column; }
            .action-btn span { display: none; }
            .action-btn.primary span { display: inline; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { align-items: flex-start; flex-direction: column; }
        }

        @media (max-width: 480px) {
            .category-mega-grid { grid-template-columns: 1fr; }
            .category-mega-foot { align-items: flex-start; flex-direction: column; }
            .promo-slide h1 { font-size: 2rem; }
            .flash-sale-title { font-size: 1.2rem; }
            .flash-product-grid { grid-template-columns: 1fr; }
            .product-grid,
            .insight-grid { grid-template-columns: 1fr; }
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
            <a href="index.php?role=user&controller=product&action=myAlerts">Dashboard của tôi</a>
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
                    <i class="fas fa-chart-line"></i><span>Dashboard</span>
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
                            <span>
                                <span class="notif-title">Thông báo giá</span>
                                <span class="notif-subtitle"><?php echo (int) ($unread_count ?? 0); ?> thông báo chưa đọc</span>
                            </span>
                            <a href="index.php?role=user&controller=product&action=myAlerts" class="notif-manage">
                                <i class="fas fa-chart-line"></i>Quản lý
                            </a>
                        </div>
                        <div class="notif-list">
                            <?php if(isset($notifications) && !empty($notifications)): ?>
                                <?php foreach($notifications as $notif): ?>
                                    <?php
                                        $isUnread = ((int) ($notif['is_read'] ?? 0) === 0);
                                        $productName = trim((string) ($notif['product_name'] ?? 'Sản phẩm đang theo dõi'));
                                        $message = trim((string) ($notif['message'] ?? 'Mở chi tiết để kiểm tra mức giá mới.'));
                                    ?>
                                    <a class="notif-item <?php echo $isUnread ? 'is-unread' : ''; ?>"
                                       href="index.php?role=user&controller=product&action=readNotification&notif_id=<?php echo (int) $notif['id']; ?>&product_id=<?php echo (int) $notif['product_id']; ?>">
                                        <span class="notif-icon"><i class="fas fa-arrow-trend-down"></i></span>
                                        <span class="notif-content">
                                            <span class="notif-topline">
                                                <span class="notif-kicker">Giá đã chạm ngưỡng</span>
                                                <?php if($isUnread): ?><span class="notif-unread-dot"></span><?php endif; ?>
                                            </span>
                                            <strong class="notif-product"><?php echo e($productName); ?></strong>
                                            <span class="notif-message"><?php echo e($message); ?></span>
                                            <span class="notif-meta">
                                                <span><i class="fas fa-clock me-1"></i><?php echo e(date('d/m/Y H:i', strtotime($notif['created_at']))); ?></span>
                                                <span class="notif-open">Xem giá</span>
                                            </span>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="notif-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <div>Chưa có thông báo giá mới.</div>
                                    <small class="d-block mt-1">Khi sản phẩm chạm mức giá bạn đặt, thông báo sẽ xuất hiện tại đây.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="notif-foot">
                            <a href="index.php?role=user&controller=product&action=myAlerts">
                                <i class="fas fa-list-check"></i>Xem dashboard giá
                            </a>
                        </div>
                    </div>
                </div>

                <a href="index.php?role=user&controller=auth&action=profile" class="action-btn">
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
    <div class="container category-nav">
        <?php if(!empty($homeCategories)): ?>
            <div class="dropdown category-menu-shell">
                <button class="category-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="outside" aria-expanded="false">
                    <i class="fas fa-bars"></i><span>Danh mục sản phẩm</span>
                </button>
                <div class="dropdown-menu category-mega">
                    <div class="category-mega-top">
                        <div class="category-mega-title">
                            <i class="fas fa-bars"></i><span>Danh mục sản phẩm</span>
                        </div>
                        <button class="category-close" type="button" data-category-close>
                            <span>Đóng</span><i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <div class="category-mega-grid">
                        <?php foreach($categoryMegaColumns as $column): ?>
                            <div>
                                <?php foreach($column as $cat): ?>
                                    <a class="category-mega-item" href="index.php?role=user&controller=product&action=search&category_id=<?php echo (int) $cat['id']; ?>">
                                        <span class="category-mega-icon"><i class="<?php echo e($cat['icon'] ?: 'fas fa-tag'); ?>"></i></span>
                                        <span class="category-mega-name"><?php echo e($cat['name']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="category-mega-foot">
                        <span><?php echo count($homeCategories); ?> danh mục đang được hỗ trợ</span>
                        <a href="index.php?role=user&controller=product&action=search">Xem toàn bộ sản phẩm <i class="fas fa-angle-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="category-divider"></div>

        <div class="category-strip">
            <?php foreach($quickCategories as $cat): ?>
                <a href="index.php?role=user&controller=product&action=search&category_id=<?php echo (int) $cat['id']; ?>" class="cat-link">
                    <?php echo e($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

<section class="promo-band">
    <div class="container promo-layout">
        <div id="homePromoCarousel" class="carousel slide promo-carousel" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#homePromoCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Khuyến mãi 1"></button>
                <button type="button" data-bs-target="#homePromoCarousel" data-bs-slide-to="1" aria-label="Khuyến mãi 2"></button>
                <button type="button" data-bs-target="#homePromoCarousel" data-bs-slide-to="2" aria-label="Khuyến mãi 3"></button>
            </div>

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="promo-slide">
                        <div>
                            <div class="promo-kicker"><i class="fas fa-bolt"></i>Flash deal đa sàn</div>
                            <h1>Săn giá tốt hơn mỗi ngày</h1>
                            <p>So sánh giá từ Tiki, Shopee và Lazada, theo dõi biến động giá trước khi quyết định mua.</p>
                            <div class="promo-actions">
                                <a href="#flashSale" class="promo-button"><i class="fas fa-fire"></i>Xem flash sale</a>
                                <a href="index.php?role=user&controller=product&action=search" class="promo-button dark"><i class="fas fa-magnifying-glass"></i>Tìm sản phẩm</a>
                            </div>
                        </div>
                        <div class="promo-products">
                            <?php foreach(array_slice($bannerProducts, 0, 2) as $p): ?>
                                <a class="promo-mini-product" href="index.php?role=user&controller=product&action=detail&id=<?php echo (int) ($p['id'] ?? 0); ?>">
                                    <?php $thumb = trim((string) ($p['thumbnail_url'] ?? '')); ?>
                                    <?php if($thumb !== ''): ?>
                                        <img src="<?php echo e($thumb); ?>" alt="<?php echo e($p['name'] ?? 'Sản phẩm'); ?>">
                                    <?php else: ?>
                                        <span class="promo-mini-icon"><i class="fas fa-box-open"></i></span>
                                    <?php endif; ?>
                                    <span>
                                        <strong><?php echo e($p['name'] ?? 'Sản phẩm'); ?></strong>
                                        <span><?php echo price_label($p['min_price'] ?? 0); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="promo-slide blue">
                        <div>
                            <div class="promo-kicker"><i class="fas fa-chart-line"></i>Phân tích biến động</div>
                            <h1>Biết lúc nào nên mua</h1>
                            <p>Hệ thống dùng lịch sử giá để gợi ý sản phẩm đang ở vùng giá đáng cân nhắc.</p>
                            <div class="promo-actions">
                                <a href="#todaySuggestions" class="promo-button"><i class="fas fa-lightbulb"></i>Gợi ý hôm nay</a>
                            </div>
                        </div>
                        <div class="promo-products">
                            <?php foreach(array_slice($todaySuggestionProducts, 0, 2) as $p): ?>
                                <a class="promo-mini-product" href="index.php?role=user&controller=product&action=detail&id=<?php echo (int) ($p['id'] ?? 0); ?>">
                                    <?php $thumb = trim((string) ($p['thumbnail_url'] ?? '')); ?>
                                    <?php if($thumb !== ''): ?>
                                        <img src="<?php echo e($thumb); ?>" alt="<?php echo e($p['name'] ?? 'Sản phẩm'); ?>">
                                    <?php else: ?>
                                        <span class="promo-mini-icon"><i class="fas fa-box-open"></i></span>
                                    <?php endif; ?>
                                    <span>
                                        <strong><?php echo e($p['name'] ?? 'Sản phẩm'); ?></strong>
                                        <span><?php echo price_label($p['min_price'] ?? 0); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="promo-slide dark">
                        <div>
                            <div class="promo-kicker"><i class="fas fa-bell"></i>Cảnh báo giá</div>
                            <h1>Không bỏ lỡ lúc giá giảm</h1>
                            <p>Lưu mức giá mong muốn và nhận thông báo khi sản phẩm chạm ngưỡng bạn đặt.</p>
                            <div class="promo-actions">
                                <a href="index.php?role=user&controller=product&action=myAlerts" class="promo-button"><i class="fas fa-bell"></i>Dashboard của tôi</a>
                            </div>
                        </div>
                        <div class="promo-products">
                            <?php foreach(array_slice($bannerProducts, 1, 2) as $p): ?>
                                <a class="promo-mini-product" href="index.php?role=user&controller=product&action=detail&id=<?php echo (int) ($p['id'] ?? 0); ?>">
                                    <?php $thumb = trim((string) ($p['thumbnail_url'] ?? '')); ?>
                                    <?php if($thumb !== ''): ?>
                                        <img src="<?php echo e($thumb); ?>" alt="<?php echo e($p['name'] ?? 'Sản phẩm'); ?>">
                                    <?php else: ?>
                                        <span class="promo-mini-icon"><i class="fas fa-box-open"></i></span>
                                    <?php endif; ?>
                                    <span>
                                        <strong><?php echo e($p['name'] ?? 'Sản phẩm'); ?></strong>
                                        <span><?php echo price_label($p['min_price'] ?? 0); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#homePromoCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Trước</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homePromoCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sau</span>
            </button>
        </div>

        <div class="promo-side">
            <a href="index.php?role=user&controller=product&action=search&sort_by=price_asc" class="promo-side-card">
                <span><strong>Giá tốt nổi bật</strong><span>Sắp xếp sản phẩm theo mức giá dễ mua nhất</span></span>
                <i class="fas fa-tags"></i>
            </a>
            <a href="index.php?role=user&controller=product&action=search&platform_filter=Tiki" class="promo-side-card">
                <span><strong>Thông tin từ Tiki</strong><span>Ưu tiên ảnh và mô tả rõ ràng cho sản phẩm</span></span>
                <i class="fas fa-store"></i>
            </a>
        </div>
    </div>
</section>

<main class="section-wrap">
    <div class="container">
        <div id="flashSale" class="flash-sale-bar">
            <div class="flash-sale-title"><i class="fas fa-bolt"></i>Flash sale hôm nay</div>
            <div class="flash-countdown" aria-label="Thời gian còn lại">
                <span id="flashHours">00</span>
                <span id="flashMinutes">00</span>
                <span id="flashSeconds">00</span>
            </div>
        </div>

        <?php if(!empty($top_deals)): ?>
            <div class="flash-product-grid">
                <?php foreach(array_slice($top_deals, 0, 4) as $p): ?>
                    <?php flash_sale_card($p); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($todaySuggestionProducts)): ?>
            <section id="todaySuggestions" class="mb-5">
                <div class="section-header">
                    <div>
                        <div class="section-kicker">Dựa trên biến động giá</div>
                        <h2 class="section-title">Sản phẩm gợi ý hôm nay</h2>
                    </div>
                    <a class="section-link" href="index.php?role=user&controller=product&action=search">Xem thêm <i class="fas fa-angle-right ms-1"></i></a>
                </div>
                <div class="insight-grid">
                    <?php foreach($todaySuggestionProducts as $p): ?>
                        <?php insight_product_card($p); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if(!empty($trending_products)): ?>
            <section class="mb-5">
                <div class="section-header">
                    <div>
                        <div class="section-kicker">Được quan tâm</div>
                        <h2 class="section-title">Sản phẩm nhiều người theo dõi</h2>
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
                        <div class="section-kicker">Khám phá thêm</div>
                        <h2 class="section-title">Có thể bạn quan tâm</h2>
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

        <?php if(empty($top_deals) && empty($todaySuggestionProducts) && empty($trending_products) && empty($new_products)): ?>
            <div class="empty-state">Chưa có sản phẩm để hiển thị.</div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>

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

function updateFlashCountdown() {
    const hoursEl = document.getElementById('flashHours');
    const minutesEl = document.getElementById('flashMinutes');
    const secondsEl = document.getElementById('flashSeconds');

    if (!hoursEl || !minutesEl || !secondsEl) {
        return;
    }

    const now = new Date();
    const end = new Date();
    end.setHours(23, 59, 59, 999);

    const remaining = Math.max(0, end.getTime() - now.getTime());
    const totalSeconds = Math.floor(remaining / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    hoursEl.textContent = String(hours).padStart(2, '0');
    minutesEl.textContent = String(minutes).padStart(2, '0');
    secondsEl.textContent = String(seconds).padStart(2, '0');
}

updateFlashCountdown();
setInterval(updateFlashCountdown, 1000);

document.querySelectorAll('[data-category-close]').forEach(function(button) {
    button.addEventListener('click', function() {
        const dropdown = button.closest('.dropdown');
        const toggle = dropdown ? dropdown.querySelector('[data-bs-toggle="dropdown"]') : null;

        if (toggle && window.bootstrap) {
            bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
        }
    });
});
</script>
</body>
</html>
