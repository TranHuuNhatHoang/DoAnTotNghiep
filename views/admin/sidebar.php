<?php
// Lấy controller và action hiện tại từ URL (nếu không có thì gán mặc định)
$c = isset($_GET['controller']) ? $_GET['controller'] : 'dashboard';
$a = isset($_GET['action']) ? $_GET['action'] : 'index';
?>
<div class="sidebar shadow">
    <div class="sidebar-header">
        <h4 class="fw-bold mb-0 text-warning"><i class="fas fa-robot me-2"></i>ADMIN PANEL</h4>
        <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Hệ Thống So Sánh Giá</small>
    </div>
    <div class="d-flex flex-column mt-3">
        <a href="index.php?role=admin&controller=dashboard&action=index" 
           class="nav-link <?php echo ($c == 'dashboard' && $a == 'index') ? 'active' : ''; ?>">
            <i class="fas fa-home me-2"></i> Tổng quan
        </a>
        
        <a href="index.php?role=admin&controller=adminCategory&action=index" 
           class="nav-link <?php echo ($c == 'adminCategory') ? 'active' : ''; ?>">
            <i class="fas fa-list me-2"></i> Quản lý Danh mục
        </a>

        <a href="index.php?role=admin&controller=adminProduct&action=index" 
           class="nav-link <?php echo ($c == 'adminProduct') ? 'active' : ''; ?>">
            <i class="fas fa-box me-2"></i> Quản lý Sản phẩm
        </a>
        
        <a href="index.php?role=admin&controller=bot&action=index" 
           class="nav-link <?php echo ($c == 'bot') ? 'active' : ''; ?>">
            <i class="fas fa-cogs me-2"></i> Quản lý Bot
        </a>
        
        <a href="index.php?role=admin&controller=dashboard&action=alerts" 
           class="nav-link <?php echo ($c == 'dashboard' && $a == 'alerts') ? 'active' : ''; ?>">
            <i class="fas fa-envelope-open-text me-2"></i> Cảnh báo giá
        </a>
        
        <hr class="text-secondary mx-3">
        <a href="index.php" class="nav-link text-info">
            <i class="fas fa-globe me-2"></i> Ra trang Web User
        </a>
        <a href="index.php?role=user&controller=auth&action=logout" class="nav-link text-danger mt-auto mb-3">
            <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
        </a>
    </div>
</div>