<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm Mới - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .sidebar { width: 250px; height: 100vh; position: fixed; background: #2c3e50; color: white; }
        .main-content { margin-left: 250px; padding: 40px; }
        .form-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-center fw-bold mb-4 text-primary">PRICE ADMIN</h4>
    <ul class="nav nav-pills flex-column mb-auto">
        <li><a href="index.php?role=admin&controller=dashboard" class="nav-link text-white"><i class="fas fa-home me-2"></i> Dashboard</a></li>
        <li><a href="index.php?role=admin&controller=bot" class="nav-link text-white"><i class="fas fa-robot me-2"></i> Quản lý Bot</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container" style="max-width: 800px;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?role=admin&controller=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Thêm sản phẩm</li>
            </ol>
        </nav>

        <div class="card form-card">
            <div class="card-body p-5">
                <h2 class="fw-bold mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>Thêm Sản Phẩm Mới</h2>
                <p class="text-muted mb-4">Nhập thông tin sản phẩm và link Tiki để bắt đầu theo dõi giá.</p>

                <form action="index.php?role=admin&controller=dashboard&action=store" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="Ví dụ: Tai nghe Sony WH-1000XM5" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mô tả ngắn</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Thông tin tóm tắt về sản phẩm..."></textarea>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-link me-2"></i>Đường dẫn Tiki (Gốc)</label>
                        <input type="url" name="tiki_url" class="form-control" placeholder="https://tiki.vn/..." required>
                        <div class="form-text">Hệ thống sẽ dùng sản phẩm Tiki làm chuẩn để tìm link Shopee tương ứng.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                            <i class="fas fa-save me-2"></i> Lưu sản phẩm & Theo dõi
                        </button>
                        <a href="index.php?role=admin&controller=dashboard" class="btn btn-link text-muted">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>