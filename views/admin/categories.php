<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* CSS đồng bộ với hệ thống Admin */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; z-index: 1000; top: 0; left: 0; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; border-bottom: 1px solid #34495e; }
        .nav-link { color: #bdc3c7; padding: 12px 20px; border-radius: 8px; transition: 0.3s; margin: 5px 15px; }
        .nav-link:hover { color: white; background: #34495e; }
        .nav-link.active { color: white; background: #3498db; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3); }
        
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Quản lý Danh mục</h3>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-2"></i>Thêm Danh mục mới
            </button>
        </div>

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="80">ID</th>
                        <th width="100">Icon</th>
                        <th>Tên danh mục</th>
                        <th>Ngày tạo</th>
                        <th width="150" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($categories)): foreach ($categories as $cat): ?>
                    <tr>
                        <td>#<?php echo $cat['id']; ?></td>
                        <td><i class="<?php echo htmlspecialchars($cat['icon']); ?> fa-2x text-primary"></i></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td class="text-muted"><?php echo date('d/m/Y', strtotime($cat['created_at'])); ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info me-2 rounded-circle" 
                                    onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cat['icon'])); ?>')"
                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal" title="Sửa danh mục">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="index.php?role=admin&controller=adminCategory&action=delete&id=<?php echo $cat['id']; ?>" 
                               class="btn btn-sm btn-outline-danger rounded-circle"
                               onclick="return confirm('Xóa danh mục này sẽ ảnh hưởng đến các sản phẩm liên quan. Tiếp tục?');" title="Xóa danh mục">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-4">Chưa có danh mục nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminCategory&action=add" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục</label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Thiết bị điện tử" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Biểu tượng (Icon)</label>
                    <select id="add_icon_select" class="form-select mb-2" onchange="toggleCustomIcon(this, 'add_icon_input')">
                        <option value="fas fa-box">📦 Hộp / Tổng hợp</option>
                        <option value="fas fa-mobile-alt">📱 Điện thoại / Phụ kiện</option>
                        <option value="fas fa-laptop">💻 Máy tính / Laptop</option>
                        <option value="fas fa-headphones">🎧 Âm thanh / Tai nghe</option>
                        <option value="fas fa-camera">📷 Máy ảnh / Quay phim</option>
                        <option value="fas fa-tv">📺 Tivi / Điện máy</option>
                        <option value="fas fa-tshirt">👕 Thời trang Nam</option>
                        <option value="fas fa-female">👗 Thời trang Nữ</option>
                        <option value="fas fa-shoe-prints">👟 Giày dép</option>
                        <option value="fas fa-glasses">🕶️ Phụ kiện / Trang sức</option>
                        <option value="fas fa-baby-carriage">👶 Mẹ & Bé</option>
                        <option value="fas fa-paw">🐾 Thú cưng</option>
                        <option value="fas fa-blender">🥣 Thiết bị gia dụng</option>
                        <option value="fas fa-home">🏠 Nhà cửa / Đời sống</option>
                        <option value="fas fa-couch">🛋️ Nội thất</option>
                        <option value="fas fa-book">📚 Sách / Văn phòng phẩm</option>
                        <option value="fas fa-gamepad">🎮 Máy chơi game / Đồ chơi</option>
                        <option value="fas fa-dumbbell">🏋️ Thể thao / Dã ngoại</option>
                        <option value="fas fa-heartbeat">💊 Sức khỏe / Y tế</option>
                        <option value="fas fa-spa">💄 Sắc đẹp / Mỹ phẩm</option>
                        <option value="fas fa-motorcycle">🏍️ Xe máy / Phụ kiện xe</option>
                        <option value="fas fa-car">🚗 Ô tô / Phụ tùng</option>
                        <option value="fas fa-apple-alt">🍎 Thực phẩm / Bách hóa</option>
                        <option value="fas fa-ticket-alt">🎫 Voucher / Dịch vụ</option>
                        <option value="custom" class="fw-bold text-primary">✏️ Lựa chọn khác (Nhập mã tự do)...</option>
                    </select>
                    <input type="text" name="icon" id="add_icon_input" class="form-control" style="display: none;" placeholder="Nhập mã FontAwesome (VD: fas fa-star)" value="fas fa-box">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminCategory&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật Danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Biểu tượng (Icon)</label>
                    <select id="edit_icon_select" class="form-select mb-2" onchange="toggleCustomIcon(this, 'edit_icon_input')">
                        <option value="fas fa-box">📦 Hộp / Tổng hợp</option>
                        <option value="fas fa-mobile-alt">📱 Điện thoại / Phụ kiện</option>
                        <option value="fas fa-laptop">💻 Máy tính / Laptop</option>
                        <option value="fas fa-headphones">🎧 Âm thanh / Tai nghe</option>
                        <option value="fas fa-camera">📷 Máy ảnh / Quay phim</option>
                        <option value="fas fa-tv">📺 Tivi / Điện máy</option>
                        <option value="fas fa-tshirt">👕 Thời trang Nam</option>
                        <option value="fas fa-female">👗 Thời trang Nữ</option>
                        <option value="fas fa-shoe-prints">👟 Giày dép</option>
                        <option value="fas fa-glasses">🕶️ Phụ kiện / Trang sức</option>
                        <option value="fas fa-baby-carriage">👶 Mẹ & Bé</option>
                        <option value="fas fa-paw">🐾 Thú cưng</option>
                        <option value="fas fa-blender">🥣 Thiết bị gia dụng</option>
                        <option value="fas fa-home">🏠 Nhà cửa / Đời sống</option>
                        <option value="fas fa-couch">🛋️ Nội thất</option>
                        <option value="fas fa-book">📚 Sách / Văn phòng phẩm</option>
                        <option value="fas fa-gamepad">🎮 Máy chơi game / Đồ chơi</option>
                        <option value="fas fa-dumbbell">🏋️ Thể thao / Dã ngoại</option>
                        <option value="fas fa-heartbeat">💊 Sức khỏe / Y tế</option>
                        <option value="fas fa-spa">💄 Sắc đẹp / Mỹ phẩm</option>
                        <option value="fas fa-motorcycle">🏍️ Xe máy / Phụ kiện xe</option>
                        <option value="fas fa-car">🚗 Ô tô / Phụ tùng</option>
                        <option value="fas fa-apple-alt">🍎 Thực phẩm / Bách hóa</option>
                        <option value="fas fa-ticket-alt">🎫 Voucher / Dịch vụ</option>
                        <option value="custom" class="fw-bold text-primary">✏️ Lựa chọn khác (Nhập mã tự do)...</option>
                    </select>
                    <input type="text" name="icon" id="edit_icon_input" class="form-control" style="display: none;" placeholder="Nhập mã FontAwesome (VD: fas fa-star)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success w-100">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Bật/tắt ô nhập mã tùy chỉnh khi chọn Dropdown
    function toggleCustomIcon(selectElement, inputId) {
        let inputField = document.getElementById(inputId);
        if (selectElement.value === 'custom') {
            inputField.style.display = 'block';
            inputField.value = ''; // Xóa rỗng để người dùng tự nhập
            inputField.focus();
        } else {
            inputField.style.display = 'none';
            inputField.value = selectElement.value; // Gán giá trị từ Dropdown vào input ẩn
        }
    }

    // Đẩy dữ liệu vào form Sửa khi bấm nút Edit
    function editCategory(id, name, icon) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        
        let selectEl = document.getElementById('edit_icon_select');
        let inputEl = document.getElementById('edit_icon_input');
        
        // Kiểm tra xem icon của danh mục này có nằm trong danh sách option không
        let optionExists = Array.from(selectEl.options).some(opt => opt.value === icon);
        
        if (optionExists && icon !== 'custom') {
            // Nếu có -> Chọn thẳng trên Dropdown, ẩn ô nhập
            selectEl.value = icon;
            inputEl.value = icon;
            inputEl.style.display = 'none';
        } else {
            // Nếu là icon lạ -> Đẩy Dropdown về "Tùy chỉnh", hiện ô nhập và gán giá trị
            selectEl.value = 'custom';
            inputEl.value = icon;
            inputEl.style.display = 'block';
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>