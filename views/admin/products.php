<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* CSS đồng bộ Sidebar */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; top: 0; left: 0; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; border-bottom: 1px solid #34495e; }
        .nav-link { color: #bdc3c7; padding: 12px 20px; border-radius: 8px; transition: 0.3s; margin: 5px 15px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .line-clamp { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Quản lý Sản phẩm</h3>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i>Thêm Sản phẩm mới
            </button>
        </div>

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">ID</th>
                        <th width="300">Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-center">Trạng thái Link</th>
                        <th>Cập nhật cuối</th>
                        <th width="150" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($products)): foreach ($products as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td>
                            <div class="fw-bold line-clamp" title="<?php echo htmlspecialchars($p['name']); ?>">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo $p['category_name'] ? htmlspecialchars($p['category_name']) : 'Chưa phân loại'; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if($p['total_active_links'] > 0): ?>
                                <span class="badge bg-success"><i class="fas fa-link me-1"></i><?php echo $p['total_active_links']; ?> sàn</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-unlink me-1"></i>Chưa có link</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted text-sm">
                            <?php echo $p['last_update'] ? date('H:i d/m/Y', strtotime($p['last_update'])) : 'Chưa quét'; ?>
                        </td>
                        <td class="text-center">
                            <a href="index.php?role=admin&controller=adminPlatform&action=index&product_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Quản lý Link Sàn">
                                <i class="fas fa-link"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-info rounded-circle me-1" 
                                    onclick="editProduct(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', '<?php echo htmlspecialchars(addslashes($p['description'])); ?>', <?php echo $p['category_id'] ? $p['category_id'] : 0; ?>)"
                                    data-bs-toggle="modal" data-bs-target="#editProductModal" title="Sửa thông tin">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="index.php?role=admin&controller=adminProduct&action=delete&id=<?php echo $p['id']; ?>" 
                               class="btn btn-sm btn-outline-danger rounded-circle"
                               onclick="return confirm('Xóa sản phẩm này sẽ xóa toàn bộ lịch sử giá và link liên quan. Chắc chắn xóa?');" title="Xóa sản phẩm">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4">Chưa có sản phẩm nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="index.php?role=admin&controller=adminProduct&action=add" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="name" class="form-control" required placeholder="Nhập tên sản phẩm chính xác để Bot dễ tìm kiếm...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="0">-- Chọn danh mục --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả tóm tắt</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Nhập cấu hình hoặc đặc điểm nhận dạng..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary px-4">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="index.php?role=admin&controller=adminProduct&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật Sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select name="category_id" id="edit_category" class="form-select">
                            <option value="0">-- Chọn danh mục --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả tóm tắt</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success px-4">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editProduct(id, name, description, category_id) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_category').value = category_id;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>