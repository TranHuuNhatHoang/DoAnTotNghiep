<?php
$links = $links ?? [];
$product = $product ?? ['name' => 'Sản phẩm'];
$product_id = (int) ($product_id ?? ($product['id'] ?? 0));

function e_admin_platform($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money_admin_platform($value) {
    if (!$value || (float) $value <= 0) {
        return 'Chưa có giá';
    }

    return number_format((float) $value, 0, ',', '.') . 'đ';
}

function platform_status_meta($status, $isActive) {
    if (!$isActive) {
        return ['label' => 'Tạm tắt', 'class' => 'text-bg-secondary'];
    }

    switch ((int) $status) {
        case 1:
            return ['label' => 'Đã cập nhật', 'class' => 'text-bg-success'];
        case 2:
            return ['label' => 'Chưa thấy giá', 'class' => 'text-bg-warning'];
        case 3:
            return ['label' => 'Lỗi quét', 'class' => 'text-bg-danger'];
        case 4:
            return ['label' => 'Cần xác minh', 'class' => 'text-bg-warning'];
        default:
            return ['label' => 'Chờ quét', 'class' => 'text-bg-light border'];
    }
}

function platform_availability_meta($status, $isActive) {
    if (!$isActive) {
        return ['label' => 'Tạm tắt', 'class' => 'text-bg-secondary'];
    }

    $map = [
        'active' => ['label' => 'Đang hoạt động', 'class' => 'text-bg-success'],
        'out_of_stock' => ['label' => 'Hết hàng', 'class' => 'text-bg-warning'],
        'temporarily_unavailable' => ['label' => 'Tạm ngừng bán', 'class' => 'text-bg-warning'],
        'discontinued' => ['label' => 'Ngừng bán/link chết', 'class' => 'text-bg-danger'],
        'invalid_url' => ['label' => 'Link lỗi', 'class' => 'text-bg-danger'],
        'fetch_error' => ['label' => 'Lỗi quét', 'class' => 'text-bg-danger'],
        'blocked_or_captcha' => ['label' => 'Bị captcha/chặn', 'class' => 'text-bg-warning'],
        'unknown' => ['label' => 'Chưa kiểm tra', 'class' => 'text-bg-light border'],
    ];

    return $map[$status] ?? $map['unknown'];
}

function admin_platform_date($value) {
    return !empty($value) ? date('H:i d/m/Y', strtotime($value)) : 'Chưa có';
}

function platform_meta($name) {
    $map = [
        'Tiki' => ['logo' => 'https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png', 'class' => 'tiki'],
        'Shopee' => ['logo' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg', 'class' => 'shopee'],
        'Lazada' => ['logo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png', 'class' => 'lazada'],
    ];

    return $map[$name] ?? ['logo' => '', 'class' => 'default'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý link sàn - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .platform-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .platform-card {
            overflow: hidden;
            border-left: 5px solid #94a3b8;
        }

        .platform-card.tiki { border-left-color: #1a94ff; }
        .platform-card.shopee { border-left-color: #ee4d2d; }
        .platform-card.lazada { border-left-color: #1a237e; }

        .platform-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .platform-logo {
            height: 28px;
            max-width: 118px;
            object-fit: contain;
        }

        .link-url {
            min-height: 64px;
            word-break: break-all;
            font-family: Consolas, Monaco, monospace;
            font-size: .86rem;
            color: #475467;
            background: #f8fafc;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            padding: 10px;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .data-row:last-child { border-bottom: 0; }

        .icon-button {
            width: 38px;
            height: 38px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
        }

        .empty-link-state {
            padding: 54px 24px;
            text-align: center;
        }

        @media (max-width: 1199px) {
            .platform-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 575px) {
            .platform-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="admin-page-head">
            <div>
                <div class="admin-page-kicker">Data sources</div>
                <h1 class="admin-page-title">Quản lý link sàn</h1>
                <p class="admin-page-desc">Gắn và bật tắt nguồn dữ liệu cho: <strong><?php echo e_admin_platform($product['name'] ?? 'Sản phẩm'); ?></strong></p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?role=admin&controller=adminProduct&action=index" class="btn btn-admin-soft px-4">
                    <i class="fas fa-arrow-left me-2"></i>Sản phẩm
                </a>
                <button class="btn btn-admin-primary px-4" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                    <i class="fas fa-plus me-2"></i>Thêm link
                </button>
            </div>
        </div>

        <?php if (empty($links)): ?>
            <section class="admin-card empty-link-state">
                <i class="fas fa-link-slash fa-3x text-muted mb-3"></i>
                <h2 class="h4 fw-bold">Chưa có link sàn</h2>
                <p class="text-muted mb-4">Bot sẽ bỏ qua sản phẩm này cho đến khi bạn thêm ít nhất một URL từ Tiki, Shopee hoặc Lazada.</p>
                <button class="btn btn-admin-primary px-4" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                    <i class="fas fa-plus me-2"></i>Thêm nguồn đầu tiên
                </button>
            </section>
        <?php else: ?>
            <section class="platform-grid">
                <?php foreach ($links as $link):
                    $meta = platform_meta($link['platform_name'] ?? '');
                    $isActive = (int) ($link['is_active'] ?? 0) === 1;
                    $statusMeta = platform_availability_meta($link['availability_status'] ?? 'unknown', $isActive);
                ?>
                    <article class="admin-card platform-card <?php echo e_admin_platform($meta['class']); ?>">
                        <div class="p-4">
                            <div class="platform-head">
                                <div>
                                    <?php if (!empty($meta['logo'])): ?>
                                        <img class="platform-logo" src="<?php echo e_admin_platform($meta['logo']); ?>" alt="<?php echo e_admin_platform($link['platform_name']); ?>">
                                    <?php else: ?>
                                        <strong><?php echo e_admin_platform($link['platform_name']); ?></strong>
                                    <?php endif; ?>
                                </div>
                                <span class="badge rounded-pill <?php echo e_admin_platform($statusMeta['class']); ?>">
                                    <?php echo e_admin_platform($statusMeta['label']); ?>
                                </span>
                            </div>

                            <div class="link-url mb-3"><?php echo e_admin_platform($link['product_url'] ?? ''); ?></div>

                            <div class="mb-3">
                                <div class="data-row">
                                    <span class="text-muted">Giá hiện tại</span>
                                    <strong><?php echo e_admin_platform(money_admin_platform($link['current_price'] ?? 0)); ?></strong>
                                </div>
                                <div class="data-row">
                                    <span class="text-muted">Quét lần cuối</span>
                                    <strong><?php echo e_admin_platform(admin_platform_date($link['last_scraped_at'] ?? null)); ?></strong>
                                </div>
                                <div class="data-row">
                                    <span class="text-muted">Kiểm tra cuối</span>
                                    <strong><?php echo e_admin_platform(admin_platform_date($link['last_checked_at'] ?? null)); ?></strong>
                                </div>
                                <div class="data-row">
                                    <span class="text-muted">Kiểm tra tiếp</span>
                                    <strong><?php echo e_admin_platform(admin_platform_date($link['next_check_at'] ?? null)); ?></strong>
                                </div>
                                <div class="data-row">
                                    <span class="text-muted">Số lần lỗi liên tiếp</span>
                                    <strong><?php echo (int) ($link['consecutive_failures'] ?? 0); ?></strong>
                                </div>
                            </div>

                            <?php if (!empty($link['error_message'])): ?>
                                <div class="alert alert-warning small py-2 mb-3">
                                    <strong>Lỗi gần nhất:</strong>
                                    <?php echo e_admin_platform($link['error_message']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <a class="btn btn-admin-soft flex-grow-1" href="<?php echo e_admin_platform($link['product_url'] ?? '#'); ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-up-right-from-square me-2"></i>Mở link
                                </a>
                                <button type="button"
                                        class="btn btn-outline-primary icon-button"
                                        data-edit-link
                                        data-id="<?php echo (int) $link['id']; ?>"
                                        data-url="<?php echo e_admin_platform($link['product_url'] ?? ''); ?>"
                                        data-active="<?php echo $isActive ? '1' : '0'; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editLinkModal"
                                        title="Sửa link">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <a href="index.php?role=admin&controller=adminPlatform&action=delete&id=<?php echo (int) $link['id']; ?>&product_id=<?php echo $product_id; ?>"
                                   class="btn btn-outline-danger icon-button"
                                   onclick="return confirm('Bạn muốn xóa link này?');"
                                   title="Xóa link">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="addLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminPlatform&action=add" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm nguồn dữ liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold">Sàn thương mại</label>
                    <select name="platform_name" class="form-select">
                        <option value="Tiki">Tiki</option>
                        <option value="Shopee">Shopee</option>
                        <option value="Lazada">Lazada</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">URL sản phẩm</label>
                    <input type="url" name="product_url" class="form-control" required placeholder="https://tiki.vn/...">
                    <div class="form-text">Dùng URL sản phẩm chi tiết, không dùng link tìm kiếm hoặc link rút gọn nếu có thể.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary w-100">Gắn link vào sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminPlatform&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật nguồn dữ liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="link_id" id="edit_link_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">URL sản phẩm</label>
                    <input type="url" name="product_url" id="edit_url" class="form-control" required>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <label class="form-check-label fw-bold" for="edit_is_active">Cho phép bot quét link này</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary w-100">Lưu cấu hình</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('[data-edit-link]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('edit_link_id').value = button.dataset.id || '';
            document.getElementById('edit_url').value = button.dataset.url || '';
            document.getElementById('edit_is_active').checked = button.dataset.active === '1';
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
