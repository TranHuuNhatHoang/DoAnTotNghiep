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

function render_search_summary($keywordValue, $products) {
    $keywordValue = trim((string) $keywordValue);
    ob_start();
    ?>
    <div class="eyebrow">Kết quả tìm kiếm</div>
    <h1 class="result-title">
        <?php echo $keywordValue !== '' ? '"' . e_search($keywordValue) . '"' : 'Tất cả sản phẩm'; ?>
    </h1>
    <div class="text-muted fw-bold mt-1"><?php echo count($products); ?> sản phẩm phù hợp</div>
    <?php
    return trim(ob_get_clean());
}

function render_search_results($products) {
    ob_start();
    ?>
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
    <?php
    return trim(ob_get_clean());
}

if (!empty($searchPartialOnly)) {
    return;
}
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

        body { min-height: 100vh; background: var(--page); color: var(--ink); font-family: "Segoe UI", Arial, sans-serif; }
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
            background:
                radial-gradient(circle at 88% 12%, rgba(247,198,0,0.2), transparent 28%),
                linear-gradient(135deg, #ffffff, #f8fafc);
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
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 20px;
            padding: 24px 0 34px;
        }

        .filter-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            position: sticky;
            top: 94px;
            align-self: start;
            max-height: calc(100vh - 112px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(16,24,40,0.07);
        }

        .filter-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            background: #fff;
        }

        .filter-heading h2 {
            font-size: 1rem;
            font-weight: 900;
            margin: 0;
        }

        .filter-scroll {
            min-height: 0;
            overflow-y: auto;
            padding: 16px 18px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .filter-scroll::-webkit-scrollbar { width: 6px; }
        .filter-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

        .filter-block { border-top: 1px solid var(--line); padding-top: 16px; margin-top: 16px; }
        .filter-block:first-child { border-top: 0; padding-top: 0; margin-top: 0; }
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
            min-height: 38px;
            padding: 4px 8px;
            border-radius: 8px;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.92rem;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .filter-option:hover { background: #f8fafc; }
        .filter-option:has(input:checked) {
            background: #eef4ff;
            color: #0b5fff;
            font-weight: 850;
        }

        .filter-option input { accent-color: var(--blue); }

        .filter-actions {
            padding: 16px 18px 18px;
            border-top: 1px solid var(--line);
            background: #fff;
            box-shadow: 0 -10px 24px rgba(16,24,40,0.04);
        }

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

        .price-inputs input.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
        }

        .price-filter-error {
            display: none;
            margin: -2px 0 10px;
            color: #d92d20;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .price-filter-error.is-visible { display: block; }

        .apply-btn, .clear-btn {
            height: 40px;
            border-radius: 8px;
            font-weight: 900;
            width: 100%;
            border: 0;
        }

        .apply-btn { background: #111827; color: #fff; }
        .apply-btn:hover { background: #1f2937; }
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
            box-shadow: 0 10px 24px rgba(16,24,40,0.05);
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
            border-color: #f7c600;
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
        [data-search-results].is-loading { opacity: .55; pointer-events: none; }
        [data-search-filter-form].is-loading .apply-btn { opacity: .75; }

        @media (max-width: 1100px) {
            .header-inner { grid-template-columns: 1fr; padding: 14px 0; }
            .brand { justify-content: center; }
            .layout { grid-template-columns: 1fr; }
            .filter-panel {
                position: static;
                max-height: none;
            }
            .filter-scroll {
                max-height: 360px;
            }
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
        <div data-search-summary>
            <?php echo render_search_summary($keywordValue, $products); ?>
        </div>
        <a href="index.php?role=user&controller=product&action=search" class="clear-btn px-3" style="width:auto;">Xem tất cả</a>
    </div>
</section>

<main class="container">
    <form action="index.php" method="GET" id="filterForm" class="layout" data-search-filter-form data-action-url="index.php">
        <input type="hidden" name="role" value="user">
        <input type="hidden" name="controller" value="product">
        <input type="hidden" name="action" value="search">
        <input type="hidden" name="keyword" value="<?php echo e_search($keywordValue); ?>">

        <aside class="filter-panel">
            <div class="filter-heading">
                <h2><i class="fas fa-sliders me-2 text-primary"></i>Bộ lọc</h2>
                <span class="text-muted small fw-bold">Lọc nhanh</span>
            </div>

            <div class="filter-scroll">
                <div class="filter-block">
                    <div class="filter-label">Danh mục</div>
                    <label class="filter-option">
                        <input type="radio" name="category_id" value="" <?php echo $selectedCategory === 0 ? 'checked' : ''; ?> onchange="if (!window.SmartPriceAjaxFilters) this.form.submit();">
                        Tất cả danh mục
                    </label>
                    <?php if(isset($categories)): foreach($categories as $cat): ?>
                        <label class="filter-option">
                            <input type="radio" name="category_id" value="<?php echo (int) $cat['id']; ?>" <?php echo $selectedCategory === (int) $cat['id'] ? 'checked' : ''; ?> onchange="if (!window.SmartPriceAjaxFilters) this.form.submit();">
                            <?php echo e_search($cat['name']); ?>
                        </label>
                    <?php endforeach; endif; ?>
                </div>

                <div class="filter-block">
                    <div class="filter-label">Sàn thương mại</div>
                    <?php foreach(['' => 'Tất cả sàn', 'Tiki' => 'Tiki', 'Shopee' => 'Shopee', 'Lazada' => 'Lazada'] as $value => $label): ?>
                        <label class="filter-option">
                            <input type="radio" name="platform_filter" value="<?php echo e_search($value); ?>" <?php echo $selectedPlatform === $value ? 'checked' : ''; ?> onchange="if (!window.SmartPriceAjaxFilters) this.form.submit();">
                            <?php echo e_search($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-actions">
                <div class="filter-label">Khoảng giá</div>
                <div class="price-inputs mb-2">
                    <input type="number" name="min_price" placeholder="Từ" value="<?php echo e_search($minPrice); ?>">
                    <input type="number" name="max_price" placeholder="Đến" value="<?php echo e_search($maxPrice); ?>">
                </div>
                <button type="submit" class="apply-btn">Áp dụng</button>

                <a href="index.php?role=user&controller=product&action=search&keyword=<?php echo urlencode($keywordValue); ?>" class="clear-btn mt-2" data-clear-filters>
                    <i class="fas fa-rotate-left me-2"></i>Xóa bộ lọc
                </a>
            </div>
        </aside>

        <section>
            <div class="toolbar">
                <div class="fw-bold text-muted">Sắp xếp sản phẩm</div>
                <select name="sort_by" onchange="if (!window.SmartPriceAjaxFilters) this.form.submit();">
                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                    <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                </select>
            </div>

            <div data-search-results>
                <?php echo render_search_results($products); ?>
            </div>
        </section>
    </form>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script>
window.SmartPriceAjaxFilters = true;

const searchFilterForm = document.querySelector('[data-search-filter-form]');
const searchSummary = document.querySelector('[data-search-summary]');
const searchResults = document.querySelector('[data-search-results]');
const clearFiltersLink = document.querySelector('[data-clear-filters]');

function getPriceInputs() {
    if (!searchFilterForm) return [];
    return [
        searchFilterForm.querySelector('input[name="min_price"]'),
        searchFilterForm.querySelector('input[name="max_price"]')
    ].filter(Boolean);
}

function priceDigits(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatPriceInputValue(value) {
    const digits = priceDigits(value);
    return digits ? Number(digits).toLocaleString('vi-VN') : '';
}

function normalizePriceInputs() {
    getPriceInputs().forEach((input) => {
        input.type = 'text';
        input.inputMode = 'numeric';
        input.autocomplete = 'off';
        input.value = formatPriceInputValue(input.value);
    });
}

function setPriceFilterError(message = '') {
    if (!searchFilterForm) return;
    let errorBox = searchFilterForm.querySelector('[data-price-filter-error]');
    const priceBox = searchFilterForm.querySelector('.price-inputs');
    if (!errorBox && priceBox) {
        errorBox = document.createElement('div');
        errorBox.className = 'price-filter-error';
        errorBox.dataset.priceFilterError = 'true';
        priceBox.insertAdjacentElement('afterend', errorBox);
    }

    getPriceInputs().forEach((input) => input.classList.toggle('is-invalid', Boolean(message)));
    if (errorBox) {
        errorBox.textContent = message;
        errorBox.classList.toggle('is-visible', Boolean(message));
    }
}

function validatePriceRange() {
    const minInput = searchFilterForm?.querySelector('input[name="min_price"]');
    const maxInput = searchFilterForm?.querySelector('input[name="max_price"]');
    const minDigits = priceDigits(minInput?.value);
    const maxDigits = priceDigits(maxInput?.value);
    const minValue = minDigits ? Number(minDigits) : null;
    const maxValue = maxDigits ? Number(maxDigits) : null;

    if (minValue !== null && maxValue !== null && maxValue < minValue) {
        setPriceFilterError('Giá đến phải lớn hơn hoặc bằng giá từ.');
        return false;
    }

    setPriceFilterError('');
    return true;
}

function buildSearchUrlFromForm(form) {
    const actionUrl = form.dataset.actionUrl || form.getAttribute('action') || 'index.php';
    const url = new URL(actionUrl, window.location.href);
    const params = new URLSearchParams();
    const keepEmptyKeys = new Set(['role', 'controller', 'action', 'keyword']);

    for (const [key, value] of new FormData(form).entries()) {
        const stringValue = key === 'min_price' || key === 'max_price'
            ? priceDigits(value)
            : String(value);
        if (stringValue !== '' || keepEmptyKeys.has(key)) {
            params.set(key, stringValue);
        }
    }

    url.search = params.toString();
    return url;
}

function setRadioValue(form, name, value) {
    const normalizedValue = value || '';
    form.querySelectorAll(`input[type="radio"][name="${name}"]`).forEach((input) => {
        input.checked = input.value === normalizedValue;
    });
}

function syncSearchFormFromUrl(url) {
    if (!searchFilterForm) return;

    const params = new URL(url, window.location.href).searchParams;
    const keyword = params.get('keyword') || '';

    const keywordInput = searchFilterForm.querySelector('input[name="keyword"]');
    if (keywordInput) keywordInput.value = keyword;

    const headerKeywordInput = document.querySelector('.search-form input[name="keyword"]');
    if (headerKeywordInput) headerKeywordInput.value = keyword;

    setRadioValue(searchFilterForm, 'category_id', params.get('category_id') || '');
    setRadioValue(searchFilterForm, 'platform_filter', params.get('platform_filter') || '');

    const minInput = searchFilterForm.querySelector('input[name="min_price"]');
    const maxInput = searchFilterForm.querySelector('input[name="max_price"]');
    const sortInput = searchFilterForm.querySelector('select[name="sort_by"]');

    if (minInput) minInput.value = formatPriceInputValue(params.get('min_price') || '');
    if (maxInput) maxInput.value = formatPriceInputValue(params.get('max_price') || '');
    if (sortInput) sortInput.value = params.get('sort_by') || 'newest';
    validatePriceRange();
}

async function readSearchJson(response) {
    try {
        return await response.json();
    } catch (error) {
        return { success: false, message: 'Máy chủ trả về dữ liệu không hợp lệ.' };
    }
}

async function loadSearchResults(url, pushUrl = true) {
    if (!searchSummary || !searchResults) {
        window.location.href = url;
        return;
    }

    searchResults.classList.add('is-loading');
    if (searchFilterForm) searchFilterForm.classList.add('is-loading');

    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await readSearchJson(response);

        if (!response.ok || !data.success) {
            window.location.href = url;
            return;
        }

        searchSummary.innerHTML = data.summary_html || '';
        searchResults.innerHTML = data.results_html || '';
        syncSearchFormFromUrl(url);

        if (pushUrl) {
            window.history.pushState({ ajaxSearch: true }, '', url);
        }
    } catch (error) {
        window.location.href = url;
    } finally {
        searchResults.classList.remove('is-loading');
        if (searchFilterForm) searchFilterForm.classList.remove('is-loading');
    }
}

if (searchFilterForm) {
    normalizePriceInputs();

    getPriceInputs().forEach((input) => {
        input.addEventListener('input', () => {
            input.value = formatPriceInputValue(input.value);
            validatePriceRange();
        });
    });

    searchFilterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validatePriceRange()) return;
        loadSearchResults(buildSearchUrlFromForm(searchFilterForm).toString(), true);
    });

    searchFilterForm.addEventListener('change', (event) => {
        if (!event.target.matches('input[type="radio"], select[name="sort_by"]')) return;
        loadSearchResults(buildSearchUrlFromForm(searchFilterForm).toString(), true);
    });
}

if (clearFiltersLink) {
    clearFiltersLink.addEventListener('click', (event) => {
        event.preventDefault();
        loadSearchResults(clearFiltersLink.href, true);
    });
}

window.addEventListener('popstate', () => {
    loadSearchResults(window.location.href, false);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
