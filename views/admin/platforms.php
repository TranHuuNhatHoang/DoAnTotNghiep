<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Link Sàn - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; top: 0; left: 0; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; border-bottom: 1px solid #34495e;}
        .nav-link { color: #bdc3c7; padding: 12px 20px; border-radius: 8px; transition: 0.3s; margin: 5px 15px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        
        .platform-card { border: none; border-radius: 12px; transition: 0.2s; border-left: 5px solid #ccc; }
        .platform-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); transform: translateY(-3px); }
        .border-tiki { border-left-color: #189eff !important; }
        .border-shopee { border-left-color: #ee4d2d !important; }
        .border-lazada { border-left-color: #00008b !important; }
        .url-text { word-break: break-all; font-family: monospace; font-size: 0.9rem; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <a href="index.php?role=admin&controller=adminProduct&action=index" class="btn btn-outline-secondary mb-4 rounded-pill">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách Sản phẩm
        </a>

        <div class="card border-0 shadow-sm mb-5" style="border-radius: 15px;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="text-muted mb-1">Cấu hình Nguồn Dữ Liệu cho sản phẩm:</h5>
                    <h3 class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($product['name']); ?></h3>
                </div>
                <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                    <i class="fas fa-plus me-2"></i>Thêm Nguồn Mới
                </button>
            </div>
        </div>

        <div class="row g-4">
            <?php if(empty($links)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-unlink fa-4x text-muted mb-3 opacity-25"></i>
                    <h5 class="text-muted">Sản phẩm này chưa được liên kết với sàn nào.</h5>
                    <p class="text-muted small">Bot cào dữ liệu sẽ bỏ qua sản phẩm này cho đến khi bạn cung cấp URL.</p>
                </div>
            <?php else: ?>
                <?php foreach($links as $link): 
                    $borderColor = 'border-secondary';
                    $logoUrl = '';
                    if($link['platform_name'] == 'Tiki') { $borderColor = 'border-tiki'; $logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png'; }
                    if($link['platform_name'] == 'Shopee') { $borderColor = 'border-shopee'; $logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg'; }
                    if($link['platform_name'] == 'Lazada') { $borderColor = 'border-lazada'; $logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png'; }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card platform-card <?php echo $borderColor; ?> shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <img src="<?php echo $logoUrl; ?>" height="25" alt="<?php echo $link['platform_name']; ?>">
                                <?php if($link['is_active'] == 1): ?>
                                    <span class="badge bg-success">Đang hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="url-text text-muted mb-3 line-clamp" title="<?php echo htmlspecialchars($link['product_url']); ?>">
                                <?php echo htmlspecialchars($link['product_url']); ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                                <div class="small text-muted">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    <?php echo $link['last_scraped_at'] ? date('H:i d/m', strtotime($link['last_scraped_at'])) : 'Chưa chạy'; ?>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-info rounded-circle me-1" title="Sửa"
                                            onclick="editLink(<?php echo $link['id']; ?>, '<?php echo $link['product_url']; ?>', <?php echo $link['is_active']; ?>)"
                                            data-bs-toggle="modal" data-bs-target="#editLinkModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="index.php?role=admin&controller=adminPlatform&action=delete&id=<?php echo $link['id']; ?>&product_id=<?php echo $product_id; ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-circle"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa đường link này?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="addLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminPlatform&action=add" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Nguồn Dữ Liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Chọn Sàn Thương Mại</label>
                    <select name="platform_name" class="form-select">
                        <option value="Shopee">Shopee</option>
                        <option value="Lazada">Lazada</option>
                        <option value="Tiki">Tiki</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Đường link URL sản phẩm</label>
                    <input type="url" name="product_url" class="form-control" required placeholder="https://shopee.vn/...">
                    <small class="text-muted mt-1 d-block">Hãy đảm bảo copy đường link chính xác từ trình duyệt.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success w-100">Gắn Link vào Bot</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminPlatform&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật Nguồn Dữ Liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="link_id" id="edit_link_id">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Đường link URL sản phẩm</label>
                    <input type="url" name="product_url" id="edit_url" class="form-control" required>
                </div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <label class="form-check-label fw-bold text-success" for="edit_is_active">Cho phép Bot cào dữ liệu từ link này</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Lưu cấu hình</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editLink(id, url, isActive) {
        document.getElementById('edit_link_id').value = id;
        document.getElementById('edit_url').value = url;
        document.getElementById('edit_is_active').checked = (isActive == 1);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>