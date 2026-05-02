<?php
$products = $products ?? [];
$categories = $categories ?? [];
$oldProductForm = $_SESSION['old_product_form'] ?? [];
$openProductModal = $_SESSION['open_product_modal'] ?? '';
if (!is_array($oldProductForm)) {
    $oldProductForm = [];
}
unset($_SESSION['old_product_form'], $_SESSION['open_product_modal']);

function e_admin_product($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_product_old_value($formData, $key, $default = '') {
    return $formData[$key] ?? $default;
}

function admin_product_old_link($formData, $platform) {
    foreach (($formData['links'] ?? []) as $link) {
        if (($link['platform'] ?? '') === $platform) {
            return $link['url'] ?? '';
        }
    }
    return '';
}

function admin_product_date($value) {
    return !empty($value) ? date('H:i d/m/Y', strtotime($value)) : 'Chưa quét';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-thumb {
            width: 58px;
            height: 58px;
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            background: #fff;
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .product-thumb i {
            color: #cbd5e1;
            font-size: 1.35rem;
        }

        .min-w-0 {
            min-width: 0;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .78rem;
            font-weight: 850;
        }

        .status-pill.ok {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.warn {
            background: #fff1f2;
            color: #be123c;
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
                <h1 class="admin-page-title">Quản lý sản phẩm</h1>
                <p class="admin-page-desc">Kiểm soát sản phẩm gốc, danh mục và trạng thái liên kết dữ liệu từ các sàn.</p>
            </div>
            <button class="btn btn-admin-primary px-4" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i>Thêm sản phẩm
            </button>
        </div>

        <?php if (!empty($_SESSION['admin_error'])): ?>
            <div class="alert alert-danger">
                <?php echo e_admin_product($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?>
            </div>
        <?php endif; ?>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 82px;">ID</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th class="text-center">Link đang bật</th>
                            <th>Cập nhật cuối</th>
                            <th class="text-center pe-4" style="width: 170px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?php echo (int) $p['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="product-thumb">
                                                <?php if (!empty($p['thumbnail_url'])): ?>
                                                    <img src="<?php echo e_admin_product($p['thumbnail_url']); ?>" alt="<?php echo e_admin_product($p['name']); ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-box-open"></i>
                                                <?php endif; ?>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="fw-bold line-clamp-2" title="<?php echo e_admin_product($p['name']); ?>">
                                                    <?php echo e_admin_product($p['name']); ?>
                                                </span>
                                                <?php if (!empty($p['description'])): ?>
                                                    <span class="text-muted small line-clamp-2"><?php echo e_admin_product($p['description']); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border">
                                            <?php echo !empty($p['category_name']) ? e_admin_product($p['category_name']) : 'Chưa phân loại'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int) ($p['total_active_links'] ?? 0) > 0): ?>
                                            <span class="status-pill ok"><i class="fas fa-link"></i><?php echo (int) $p['total_active_links']; ?> sàn</span>
                                        <?php else: ?>
                                            <span class="status-pill warn"><i class="fas fa-triangle-exclamation"></i>Chưa có link</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><?php echo e_admin_product(admin_product_date($p['last_update'] ?? null)); ?></td>
                                    <td class="pe-4">
                                        <div class="table-actions">
                                            <a href="index.php?role=admin&controller=adminPlatform&action=index&product_id=<?php echo (int) $p['id']; ?>"
                                               class="btn btn-outline-primary icon-button"
                                               title="Quản lý link sàn">
                                                <i class="fas fa-link"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-secondary icon-button"
                                                    data-edit-product
                                                    data-id="<?php echo (int) $p['id']; ?>"
                                                    data-name="<?php echo e_admin_product($p['name']); ?>"
                                                    data-description="<?php echo e_admin_product($p['description'] ?? ''); ?>"
                                                    data-category-id="<?php echo (int) ($p['category_id'] ?? 0); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editProductModal"
                                                    title="Sửa sản phẩm">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="index.php?role=admin&controller=adminProduct&action=delete&id=<?php echo (int) $p['id']; ?>"
                                               class="btn btn-outline-danger icon-button"
                                               onclick="return confirm('Xóa sản phẩm này? Lịch sử giá và link liên quan cũng sẽ bị xóa.');"
                                               title="Xóa sản phẩm">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
                                    Chưa có sản phẩm nào.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="index.php?role=admin&controller=adminProduct&action=add" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="name" class="form-control" required placeholder="Nhập tên sản phẩm chính xác để bot tìm tốt hơn" value="<?php echo e_admin_product(admin_product_old_value($oldProductForm, 'name')); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="0">Chọn danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int) $cat['id']; ?>" <?php echo (int) admin_product_old_value($oldProductForm, 'category_id', 0) === (int) $cat['id'] ? 'selected' : ''; ?>><?php echo e_admin_product($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Mô tả tóm tắt</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Nhập cấu hình, phiên bản hoặc điểm nhận dạng"><?php echo e_admin_product(admin_product_old_value($oldProductForm, 'description')); ?></textarea>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="fw-bold mb-2">Link sàn để kiểm tra trùng</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Link Tiki</label>
                                    <input type="url" name="tiki_url" class="form-control" placeholder="https://tiki.vn/..." value="<?php echo e_admin_product(admin_product_old_link($oldProductForm, 'Tiki')); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Link Shopee</label>
                                    <input type="url" name="shopee_url" class="form-control" placeholder="https://shopee.vn/..." value="<?php echo e_admin_product(admin_product_old_link($oldProductForm, 'Shopee')); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Link Lazada</label>
                                    <input type="url" name="lazada_url" class="form-control" placeholder="https://www.lazada.vn/..." value="<?php echo e_admin_product(admin_product_old_link($oldProductForm, 'Lazada')); ?>">
                                </div>
                            </div>
                            <div class="form-text mt-2">Hệ thống sẽ kiểm tra trùng link/product id trước khi tạo sản phẩm.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary px-4">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="index.php?role=admin&controller=adminProduct&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Tên sản phẩm</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Danh mục</label>
                        <select name="category_id" id="edit_category" class="form-select">
                            <option value="0">Chọn danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int) $cat['id']; ?>"><?php echo e_admin_product($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Mô tả tóm tắt</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary px-4">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('[data-edit-product]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('edit_id').value = button.dataset.id || '';
            document.getElementById('edit_name').value = button.dataset.name || '';
            document.getElementById('edit_description').value = button.dataset.description || '';
            document.getElementById('edit_category').value = button.dataset.categoryId || '0';
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($openProductModal === 'add'): ?>
<script>
    (() => {
        const addModal = document.getElementById('addProductModal');
        if (addModal && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(addModal).show();
        }
    })();
</script>
<?php endif; ?>
</body>
</html>
