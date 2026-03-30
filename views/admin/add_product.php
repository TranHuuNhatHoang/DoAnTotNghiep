<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm Mới - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Cấu hình CSS cho Sidebar (Kế thừa cho file include) */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; }
        .nav-link { color: #bdc3c7; padding: 12px 20px; transition: 0.3s; margin: 5px 15px; border-radius: 8px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; transition: all 0.3s; }
        
        /* Form Card Styling */
        .form-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container" style="max-width: 800px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?role=admin&controller=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Thêm sản phẩm</li>
            </ol>
        </nav>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> <strong>Tuyệt vời!</strong> Đã thêm sản phẩm thành công. Hãy qua mục "Quản lý Bot" và chạy <strong>Bot Tìm Link Tự Động</strong> để quét Shopee và Lazada nhé.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Lỗi!</strong> Không thể thêm sản phẩm, vui lòng kiểm tra lại dữ liệu đầu vào.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card form-card mt-2">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>Thêm Sản Phẩm Mới</h2>
                <p class="text-muted mb-4">Nhập thông tin sản phẩm và link Tiki để hệ thống bắt đầu theo dõi giá đa sàn.</p>

                <form action="index.php?role=admin&controller=dashboard&action=store" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="Ví dụ: Tai nghe Sony WH-1000XM5" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả ngắn</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Thông tin tóm tắt về sản phẩm..."></textarea>
                    </div>

                    <div class="mb-4 p-4 bg-light rounded-4 border">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-link me-2"></i>Đường dẫn Tiki (Sàn tiêu chuẩn)</label>
                        <input type="url" name="tiki_url" class="form-control mb-2" placeholder="https://tiki.vn/..." required>
                        <div class="form-text text-muted small">
                            Hệ thống sẽ dùng tên sản phẩm và dữ liệu từ Tiki làm mỏ neo chuẩn để <strong>Fuzzy Matcher Bot</strong> tự động đi tìm link khớp trên Shopee và Lazada.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> Lưu sản phẩm vào Hệ thống
                        </button>
                        <a href="index.php?role=admin&controller=dashboard" class="btn btn-link text-muted text-decoration-none mt-2">Hủy bỏ và quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>