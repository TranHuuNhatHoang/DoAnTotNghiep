<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bot - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Sidebar Styling (Đồng bộ với Dashboard) */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        
        .nav-link { color: #bdc3c7; padding: 12px 20px; border-radius: 8px; transition: 0.3s; margin: 5px 15px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }

        /* Bot Cards */
        .bot-card { border: none; border-radius: 15px; transition: 0.3s; overflow: hidden; }
        .bot-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        /* Terminal Styling */
        .terminal-box { 
            background: #1e1e1e; 
            color: #00ff00; 
            padding: 20px; 
            border-radius: 12px; 
            font-family: 'Consolas', 'Monaco', monospace; 
            font-size: 0.85rem; 
            max-height: 350px; 
            overflow-y: auto;
            border: 1px solid #333;
            box-shadow: inset 0 0 10px #000;
        }
        .terminal-header {
            background: #333;
            color: #fff;
            padding: 8px 15px;
            border-radius: 12px 12px 0 0;
            font-size: 0.75rem;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="sidebar-header mb-3">
        <h4 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-line me-2"></i>PRICE ADMIN</h4>
        <small class="text-muted">v2.1 Professional</small>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php?role=admin&controller=dashboard&action=index" class="nav-link">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="index.php?role=admin&controller=bot&action=index" class="nav-link active">
                <i class="fas fa-robot me-2"></i> Quản lý Bot
            </a>
        </li>
        <li>
            <a href="#" class="nav-link"><i class="fas fa-box me-2"></i> Tất cả sản phẩm</a>
        </li>
    </ul>
    
    <hr class="mx-3 bg-secondary">
    <div class="px-3 pb-4">
        <a href="index.php?role=user&controller=product&action=index" class="btn btn-outline-danger w-100 rounded-pill shadow-sm">
            <i class="fas fa-sign-out-alt me-2"></i> Thoát về trang User
        </a>
    </div>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="mb-4">
            <h2 class="fw-bold">Trung Tâm Điều Khiển Bot</h2>
            <p class="text-muted">Kích hoạt các kịch bản Python Crawler để cập nhật dữ liệu thời gian thực.</p>
        </div>

        <?php 
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (isset($_SESSION['bot_message'])): ?>
            <div class="mb-5">
                <div class="terminal-header">
                    <span><i class="fas fa-terminal me-2"></i> Hệ thống phản hồi: Last Execution Output</span>
                    <span><i class="fas fa-circle text-danger me-1"></i> <i class="fas fa-circle text-warning me-1"></i> <i class="fas fa-circle text-success"></i></span>
                </div>
                <div class="terminal-box shadow">
                    <?php echo $_SESSION['bot_message']; ?>
                </div>
            </div>
        <?php unset($_SESSION['bot_message']); endif; ?>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card bot-card shadow-sm h-100 border-0">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 p-3 bg-light rounded-circle d-inline-block">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg" height="40">
                        </div>
                        <h5 class="fw-bold">Shopee Crawler</h5>
                        <p class="text-muted small px-2">Sử dụng Selenium để vượt tường lửa, lấy giá thực tế và lịch sử bán hàng từ Shopee.</p>
                        <hr class="my-4">
                        <a href="index.php?role=admin&controller=bot&action=run&type=shopee" class="btn btn-lg w-100 rounded-pill text-white shadow-sm" style="background-color: #ee4d2d;">
                            <i class="fas fa-play me-2"></i> Chạy Shopee Bot
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bot-card shadow-sm h-100 border-0">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 p-3 bg-light rounded-circle d-inline-block">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png" height="40">
                        </div>
                        <h5 class="fw-bold">Tiki Scraper</h5>
                        <p class="text-muted small px-2">Cào dữ liệu hiệu suất cao thông qua Tiki API. Cập nhật giá nhanh chóng và chính xác.</p>
                        <hr class="my-4">
                        <a href="index.php?role=admin&controller=bot&action=run&type=tiki" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm">
                            <i class="fas fa-play me-2"></i> Chạy Tiki Bot
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bot-card shadow-sm h-100 border-0" style="border-top: 5px solid #3498db !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 p-3 bg-soft-primary rounded-circle d-inline-block text-primary">
                            <i class="fas fa-link fa-2x"></i>
                        </div>
                        <h5 class="fw-bold text-primary">Fuzzy Matcher</h5>
                        <p class="text-muted small px-2">Thuật toán tìm kiếm link Shopee tương ứng dựa trên tên sản phẩm Tiki đã có sẵn.</p>
                        <hr class="my-4">
                        <a href="index.php?role=admin&controller=bot&action=run&type=matcher" class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm">
                            <i class="fas fa-search-plus me-2"></i> Tìm link tự động
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
    <div class="card bot-card shadow-sm h-100 border-0">
        <div class="card-body text-center p-4">
            <div class="mb-3 p-3 bg-light rounded-circle d-inline-block">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png" height="30">
            </div>
            <h5 class="fw-bold">Lazada Crawler</h5>
            <p class="text-muted small px-2">Dò tìm giá trực diện, hỗ trợ vượt Captcha trượt và lưu lịch sử giá Lazada.</p>
            <hr class="my-4">
            <a href="index.php?role=admin&controller=bot&action=run&type=lazada" class="btn btn-lg w-100 rounded-pill text-white shadow-sm" style="background-color: #00008b;">
                <i class="fas fa-play me-2"></i> Chạy Lazada Bot
            </a>
        </div>
    </div>
</div>

        <div class="mt-5 p-4 bg-white rounded-4 shadow-sm">
            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-info me-2"></i>Hướng dẫn vận hành</h6>
            <ul class="text-muted small mb-0">
                <li>Bấm <strong>Tìm link tự động</strong> sau khi bạn vừa thêm một sản phẩm mới từ Dashboard.</li>
                <li>Chạy các <strong>Bot Crawler</strong> định kỳ hoặc khi cần cập nhật biểu đồ giá mới nhất.</li>
                <li>Nếu cửa sổ Chrome không hiện ra, Bot đang chạy ở chế độ <code>headless</code> (ẩn).</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bắt sự kiện khi click vào bất kỳ nút chạy Bot nào
    document.querySelectorAll('.btn[href*="action=run"]').forEach(button => {
        button.addEventListener('click', function(e) {
            // Đổi nội dung nút thành trạng thái Đang chạy
            let originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang chạy Bot... Vui lòng đợi!';
            this.classList.add('disabled'); // Làm mờ nút, chống bấm 2 lần
            this.style.opacity = '0.7';
            
            // Hiển thị một khung thông báo tạm thời báo cho Admin biết hệ thống đang xử lý
            let terminalArea = document.querySelector('.mb-5');
            if(!terminalArea) {
                let infoDiv = document.createElement('div');
                infoDiv.innerHTML = `
                    <div class="alert alert-info shadow-sm">
                        <i class="fas fa-cog fa-spin me-2"></i> <strong>Hệ thống đang thực thi mã Python ngầm!</strong> Quá trình này có thể mất vài phút tùy số lượng sản phẩm. Vui lòng không làm mới (F5) trang...
                    </div>`;
                document.querySelector('.row.g-4').insertAdjacentElement('beforebegin', infoDiv);
            }
        });
    });
</script>
</body>
</html>