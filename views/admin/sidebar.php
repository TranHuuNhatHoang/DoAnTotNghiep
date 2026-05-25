<?php
$c = strtolower($_GET['controller'] ?? 'dashboard');
$a = strtolower($_GET['action'] ?? 'index');

function admin_nav_active($condition) {
    return $condition ? 'active' : '';
}
?>
<style>
    :root {
        --admin-sidebar-width: 270px;
        --admin-ink: #101828;
        --admin-muted: #667085;
        --admin-line: #e4e7ec;
        --admin-soft: #f5f7fb;
        --admin-dark: #101828;
        --admin-yellow: #f7c600;
        --admin-blue: #2563eb;
    }

    body {
        background: var(--admin-soft) !important;
        color: var(--admin-ink);
        font-family: "Segoe UI", Arial, sans-serif !important;
    }

    .sidebar {
        width: var(--admin-sidebar-width) !important;
        height: 100vh !important;
        position: fixed !important;
        inset: 0 auto 0 0 !important;
        z-index: 1000 !important;
        padding: 18px 14px !important;
        background: #0f172a !important;
        color: #fff !important;
        box-shadow: 18px 0 34px rgba(15, 23, 42, .14) !important;
        overflow-y: auto;
    }

    .sidebar-header {
        padding: 4px 6px 18px !important;
        text-align: left !important;
        background: transparent !important;
        border-bottom: 1px solid rgba(255,255,255,.1) !important;
    }

    .admin-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        text-decoration: none;
    }

    .admin-brand-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        background: var(--admin-yellow);
        color: #111827;
        flex: 0 0 auto;
    }

    .admin-brand-title {
        display: block;
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .admin-brand-subtitle {
        display: block;
        margin-top: 3px;
        color: #94a3b8;
        font-size: .74rem;
        font-weight: 700;
    }

    .sidebar-section {
        margin: 18px 6px 8px;
        color: #94a3b8;
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 12px;
    }

    .sidebar .nav-link {
        display: flex !important;
        align-items: center !important;
        gap: 11px !important;
        margin: 0 !important;
        padding: 11px 12px !important;
        min-height: 44px;
        color: #cbd5e1 !important;
        border: 1px solid transparent;
        border-radius: 8px !important;
        font-weight: 750;
        text-decoration: none;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .sidebar .nav-link i {
        width: 20px;
        text-align: center;
        color: inherit;
    }

    .sidebar .nav-link:hover {
        background: rgba(255,255,255,.08) !important;
        border-color: rgba(255,255,255,.08);
        color: #fff !important;
    }

    .sidebar .nav-link.active {
        background: var(--admin-yellow) !important;
        border-color: var(--admin-yellow);
        color: #111827 !important;
        box-shadow: none !important;
    }

    .sidebar .nav-link.danger {
        color: #fecdd3 !important;
    }

    .sidebar .nav-link.user-site {
        color: #bfdbfe !important;
    }

    .sidebar-divider {
        border-color: rgba(255,255,255,.12);
        margin: 16px 6px;
    }

    .main-content {
        margin-left: var(--admin-sidebar-width) !important;
        padding: 34px !important;
    }

    .admin-page-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .admin-page-kicker {
        color: var(--admin-blue);
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .admin-page-title {
        margin: 0;
        color: var(--admin-ink);
        font-size: 1.75rem;
        line-height: 1.15;
        font-weight: 950;
        letter-spacing: 0;
    }

    .admin-page-desc {
        margin: 6px 0 0;
        color: var(--admin-muted);
    }

    .admin-card {
        background: #fff;
        border: 1px solid var(--admin-line);
        border-radius: 8px;
        box-shadow: 0 12px 28px rgba(16,24,40,.06);
    }

    .admin-table th {
        color: #475467;
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
        background: #f8fafc !important;
        border-bottom: 1px solid var(--admin-line);
        white-space: nowrap;
    }

    .admin-table td {
        vertical-align: middle;
        border-color: #eef2f7;
    }

    .btn-admin-primary {
        border: 1px solid var(--admin-dark) !important;
        background: var(--admin-dark) !important;
        color: #fff !important;
        border-radius: 8px !important;
        font-weight: 850 !important;
        min-height: 42px;
    }

    .btn-admin-soft {
        border: 1px solid var(--admin-line) !important;
        background: #fff !important;
        color: var(--admin-ink) !important;
        border-radius: 8px !important;
        font-weight: 800 !important;
        min-height: 42px;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 991px) {
        .sidebar {
            position: static !important;
            width: 100% !important;
            height: auto !important;
            padding: 14px !important;
        }

        .sidebar-nav {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .main-content {
            margin-left: 0 !important;
            padding: 22px !important;
        }

        .admin-page-head {
            align-items: stretch;
            flex-direction: column;
        }
    }

    @media (max-width: 575px) {
        .sidebar-nav {
            grid-template-columns: 1fr;
        }
    }
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <a class="admin-brand" href="index.php?role=admin&controller=dashboard&action=index">
            <span class="admin-brand-icon"><i class="fas fa-chart-line"></i></span>
            <span>
                <span class="admin-brand-title">Admin Panel</span>
                <span class="admin-brand-subtitle">Hệ thống so sánh giá</span>
            </span>
        </a>
    </div>

    <div class="sidebar-section">Quản trị</div>
    <nav class="sidebar-nav" aria-label="Admin navigation">
        <a href="index.php?role=admin&controller=dashboard&action=index"
           class="nav-link <?php echo admin_nav_active($c === 'dashboard' && $a === 'index'); ?>">
            <i class="fas fa-gauge-high"></i> Tổng quan
        </a>
        <a href="index.php?role=admin&controller=adminCategory&action=index"
           class="nav-link <?php echo admin_nav_active($c === 'admincategory'); ?>">
            <i class="fas fa-layer-group"></i> Danh mục
        </a>
        <a href="index.php?role=admin&controller=adminProduct&action=index"
           class="nav-link <?php echo admin_nav_active($c === 'adminproduct'); ?>">
            <i class="fas fa-box"></i> Sản phẩm
        </a>
        <a href="index.php?role=admin&controller=adminPlatform&action=index"
           class="nav-link <?php echo admin_nav_active($c === 'adminplatform'); ?>">
            <i class="fas fa-link"></i> Link sàn
        </a>
        <a href="index.php?role=admin&controller=adminUser&action=index"
           class="nav-link <?php echo admin_nav_active($c === 'adminuser'); ?>">
            <i class="fas fa-users"></i> Quản lý người dùng
        </a>
        <a href="index.php?role=admin&controller=bot&action=index"
           class="nav-link <?php echo admin_nav_active($c === 'bot'); ?>">
            <i class="fas fa-robot"></i> Bot crawler
        </a>
        <a href="index.php?role=admin&controller=dashboard&action=alerts"
           class="nav-link <?php echo admin_nav_active($c === 'dashboard' && $a === 'alerts'); ?>">
            <i class="fas fa-bell"></i> Cảnh báo giá
        </a>
    </nav>

    <hr class="sidebar-divider">

    <nav class="sidebar-nav" aria-label="Quick actions">
        <a href="index.php" class="nav-link user-site">
            <i class="fas fa-globe"></i> Xem trang user
        </a>
        <a href="index.php?role=user&controller=auth&action=logout" class="nav-link danger">
            <i class="fas fa-right-from-bracket"></i> Đăng xuất
        </a>
    </nav>
</aside>
