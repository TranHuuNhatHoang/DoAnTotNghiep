<?php
$categories = $categories ?? [];

function e_admin_category($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$iconOptions = [
    'fas fa-box' => 'Tổng hợp',
    'fas fa-mobile-alt' => 'Điện thoại / Phụ kiện',
    'fas fa-laptop' => 'Laptop / Máy tính',
    'fas fa-headphones' => 'Âm thanh',
    'fas fa-camera' => 'Máy ảnh',
    'fas fa-tv' => 'Tivi / Điện máy',
    'fas fa-tshirt' => 'Thời trang',
    'fas fa-shoe-prints' => 'Giày dép',
    'fas fa-blender' => 'Gia dụng',
    'fas fa-home' => 'Nhà cửa',
    'fas fa-book' => 'Sách',
    'fas fa-gamepad' => 'Game / Đồ chơi',
    'fas fa-dumbbell' => 'Thể thao',
    'fas fa-heartbeat' => 'Sức khỏe',
    'fas fa-spa' => 'Làm đẹp',
    'fas fa-car' => 'Xe / Phụ kiện',
    'fas fa-apple-alt' => 'Bách hóa',
    'fas fa-ticket-alt' => 'Voucher / Dịch vụ',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #eff6ff;
            color: #2563eb;
        }

        .table-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .icon-button {
            width: 38px;
            height: 38px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="admin-page-head">
            <div>
                <div class="admin-page-kicker">Catalog</div>
                <h1 class="admin-page-title">Quản lý danh mục</h1>
                <p class="admin-page-desc">Sắp xếp nhóm sản phẩm để người dùng lọc và tìm kiếm nhanh hơn.</p>
            </div>
            <button class="btn btn-admin-primary px-4" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-2"></i>Thêm danh mục
            </button>
        </div>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 90px;">ID</th>
                            <th style="width: 90px;">Icon</th>
                            <th>Tên danh mục</th>
                            <th>Ngày tạo</th>
                            <th class="text-center pe-4" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?php echo (int) $cat['id']; ?></td>
                                    <td>
                                        <span class="category-icon">
                                            <i class="<?php echo e_admin_category($cat['icon'] ?: 'fas fa-box'); ?>"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo e_admin_category($cat['name']); ?></div>
                                        <div class="text-muted small"><?php echo e_admin_category($cat['icon'] ?: 'fas fa-box'); ?></div>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo !empty($cat['created_at']) ? date('d/m/Y', strtotime($cat['created_at'])) : 'Chưa rõ'; ?>
                                    </td>
                                    <td class="pe-4">
                                        <div class="table-actions">
                                            <button type="button"
                                                    class="btn btn-outline-primary icon-button"
                                                    data-edit-category
                                                    data-id="<?php echo (int) $cat['id']; ?>"
                                                    data-name="<?php echo e_admin_category($cat['name']); ?>"
                                                    data-icon="<?php echo e_admin_category($cat['icon']); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editCategoryModal"
                                                    title="Sửa danh mục">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="index.php?role=admin&controller=adminCategory&action=delete&id=<?php echo (int) $cat['id']; ?>"
                                               class="btn btn-outline-danger icon-button"
                                               onclick="return confirm('Xóa danh mục này? Các sản phẩm liên quan có thể bị ảnh hưởng.');"
                                               title="Xóa danh mục">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-layer-group fa-2x mb-3 d-block"></i>
                                    Chưa có danh mục nào.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminCategory&action=add" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục</label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Điện thoại" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Biểu tượng</label>
                    <select id="add_icon_select" class="form-select mb-2" onchange="syncIconInput(this, 'add_icon_input')">
                        <?php foreach ($iconOptions as $icon => $label): ?>
                            <option value="<?php echo e_admin_category($icon); ?>"><?php echo e_admin_category($label); ?></option>
                        <?php endforeach; ?>
                        <option value="custom">Nhập mã FontAwesome khác</option>
                    </select>
                    <input type="text" name="icon" id="add_icon_input" class="form-control d-none" value="fas fa-box" placeholder="Ví dụ: fas fa-star">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary w-100">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminCategory&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Biểu tượng</label>
                    <select id="edit_icon_select" class="form-select mb-2" onchange="syncIconInput(this, 'edit_icon_input')">
                        <?php foreach ($iconOptions as $icon => $label): ?>
                            <option value="<?php echo e_admin_category($icon); ?>"><?php echo e_admin_category($label); ?></option>
                        <?php endforeach; ?>
                        <option value="custom">Nhập mã FontAwesome khác</option>
                    </select>
                    <input type="text" name="icon" id="edit_icon_input" class="form-control d-none" placeholder="Ví dụ: fas fa-star">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary w-100">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function syncIconInput(selectElement, inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        if (selectElement.value === 'custom') {
            input.classList.remove('d-none');
            input.value = '';
            input.focus();
            return;
        }

        input.classList.add('d-none');
        input.value = selectElement.value;
    }

    function setIconControl(selectId, inputId, icon) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (!select || !input) return;

        const exists = Array.from(select.options).some((option) => option.value === icon);
        if (exists) {
            select.value = icon;
            input.value = icon;
            input.classList.add('d-none');
        } else {
            select.value = 'custom';
            input.value = icon || '';
            input.classList.remove('d-none');
        }
    }

    document.querySelectorAll('[data-edit-category]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('edit_id').value = button.dataset.id || '';
            document.getElementById('edit_name').value = button.dataset.name || '';
            setIconControl('edit_icon_select', 'edit_icon_input', button.dataset.icon || 'fas fa-box');
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
