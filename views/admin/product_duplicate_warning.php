<?php
$formData = $formData ?? ['name' => '', 'description' => '', 'category_id' => 0, 'links' => []];
$duplicateMode = $duplicateMode ?? 'similar';
$duplicates = $duplicates ?? [];
$similarCandidates = $similarCandidates ?? [];

function e_duplicate($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function duplicate_money($value) {
    return !empty($value) && (float) $value > 0 ? number_format((float) $value, 0, ',', '.') . 'đ' : 'Chưa có giá';
}

function duplicate_hidden_fields($formData) {
    $links = ['Tiki' => 'tiki_url', 'Shopee' => 'shopee_url', 'Lazada' => 'lazada_url'];
    echo '<input type="hidden" name="name" value="' . e_duplicate($formData['name'] ?? '') . '">';
    echo '<input type="hidden" name="description" value="' . e_duplicate($formData['description'] ?? '') . '">';
    echo '<input type="hidden" name="category_id" value="' . (int) ($formData['category_id'] ?? 0) . '">';
    foreach (($formData['links'] ?? []) as $link) {
        $field = $links[$link['platform']] ?? null;
        if ($field) {
            echo '<input type="hidden" name="' . e_duplicate($field) . '" value="' . e_duplicate($link['url'] ?? '') . '">';
        }
    }
}

$candidateIds = array_map(static function ($item) {
    return (int) ($item['id'] ?? 0);
}, $similarCandidates);
$candidateIds = array_filter($candidateIds);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảnh báo sản phẩm trùng - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .candidate-card {
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            background: #fff;
        }

        .platform-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .platform-badge {
            border: 1px solid #e4e7ec;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .82rem;
            background: #f8fafc;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="admin-page-head">
            <div>
                <div class="admin-page-kicker">Duplicate check</div>
                <h1 class="admin-page-title">
                    <?php echo $duplicateMode === 'exact' ? 'Phát hiện link đã tồn tại' : 'Có thể sản phẩm đã tồn tại'; ?>
                </h1>
                <p class="admin-page-desc">
                    Sản phẩm đang nhập: <strong><?php echo e_duplicate($formData['name'] ?? ''); ?></strong>
                </p>
            </div>
            <a href="index.php?role=admin&controller=adminProduct&action=index" class="btn btn-admin-soft px-4">
                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
            </a>
        </div>

        <?php if ($duplicateMode === 'exact'): ?>
            <section class="admin-card p-4">
                <div class="alert alert-danger">
                    Link hoặc mã sản phẩm trên sàn đã được gắn với sản phẩm khác. Hệ thống không cho tạo sản phẩm mới trực tiếp trong trường hợp này.
                </div>

                <?php foreach ($duplicates as $duplicate): ?>
                    <div class="candidate-card">
                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                            <div>
                                <div class="fw-bold h5 mb-1"><?php echo e_duplicate($duplicate['product_name'] ?? 'Sản phẩm đã có'); ?></div>
                                <div class="text-muted mb-2">
                                    #<?php echo (int) ($duplicate['product_id'] ?? 0); ?>
                                    <?php if (!empty($duplicate['category_name'])): ?>
                                        · <?php echo e_duplicate($duplicate['category_name']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted">
                                    Trùng sàn: <?php echo e_duplicate($duplicate['submitted_platform'] ?? $duplicate['platform_name'] ?? ''); ?><br>
                                    Link nhập: <?php echo e_duplicate($duplicate['submitted_url'] ?? ''); ?><br>
                                    Product id kiểm tra: <?php echo e_duplicate($duplicate['platform_product_id_checked'] ?? 'Không có'); ?><br>
                                    URL hash: <?php echo e_duplicate($duplicate['url_hash_checked'] ?? 'Không có'); ?>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a class="btn btn-outline-primary" href="index.php?role=admin&controller=adminPlatform&action=index&product_id=<?php echo (int) ($duplicate['product_id'] ?? 0); ?>">
                                    Đi tới sản phẩm đã có
                                </a>
                                <form method="POST" action="index.php?role=admin&controller=adminProduct&action=attachDuplicateLinks">
                                    <?php duplicate_hidden_fields($formData); ?>
                                    <input type="hidden" name="target_product_id" value="<?php echo (int) ($duplicate['product_id'] ?? 0); ?>">
                                    <button class="btn btn-admin-primary w-100" type="submit">Gắn link vào sản phẩm đã có</button>
                                </form>
                                <form method="POST" action="index.php?role=admin&controller=adminProduct&action=restoreDuplicateForm">
                                    <?php duplicate_hidden_fields($formData); ?>
                                    <button class="btn btn-admin-soft w-100" type="submit">Quay lại sửa link</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="admin-card p-4">
                <div class="alert alert-warning">
                    Tên sản phẩm gần giống với một số sản phẩm đã có. Chỉ chọn “vẫn tạo mới” nếu bạn chắc chắn đây là sản phẩm khác.
                </div>

                <?php foreach ($similarCandidates as $candidate): ?>
                    <div class="candidate-card">
                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                            <div class="flex-grow-1">
                                <div class="fw-bold h5 mb-1"><?php echo e_duplicate($candidate['name'] ?? ''); ?></div>
                                <div class="text-muted mb-2">
                                    #<?php echo (int) ($candidate['id'] ?? 0); ?>
                                    <?php if (!empty($candidate['category_name'])): ?>
                                        · <?php echo e_duplicate($candidate['category_name']); ?>
                                    <?php endif; ?>
                                    · Giá hiện tại: <?php echo e_duplicate(duplicate_money($candidate['min_price'] ?? 0)); ?>
                                    · Điểm giống: <?php echo (int) ($candidate['similarity_score'] ?? 0); ?>%
                                </div>
                                <div class="platform-list">
                                    <?php foreach (($candidate['platform_links'] ?? []) as $link): ?>
                                        <a class="platform-badge text-decoration-none" href="<?php echo e_duplicate($link['product_url'] ?? '#'); ?>" target="_blank" rel="noopener">
                                            <?php echo e_duplicate($link['platform_name'] ?? ''); ?>
                                            · <?php echo e_duplicate(duplicate_money($link['current_price'] ?? 0)); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <form method="POST" action="index.php?role=admin&controller=adminProduct&action=attachDuplicateLinks">
                                    <?php duplicate_hidden_fields($formData); ?>
                                    <input type="hidden" name="target_product_id" value="<?php echo (int) ($candidate['id'] ?? 0); ?>">
                                    <button class="btn btn-admin-primary w-100" type="submit">Gắn link vào sản phẩm này</button>
                                </form>
                                <a class="btn btn-outline-primary" href="index.php?role=admin&controller=adminPlatform&action=index&product_id=<?php echo (int) ($candidate['id'] ?? 0); ?>">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <form method="POST" action="index.php?role=admin&controller=adminProduct&action=add">
                        <?php duplicate_hidden_fields($formData); ?>
                        <input type="hidden" name="force_create" value="1">
                        <input type="hidden" name="candidate_ids" value="<?php echo e_duplicate(implode(',', $candidateIds)); ?>">
                        <button class="btn btn-danger" type="submit">
                            Tôi xác nhận đây là sản phẩm khác, vẫn tạo mới
                        </button>
                    </form>
                    <form method="POST" action="index.php?role=admin&controller=adminProduct&action=restoreDuplicateForm">
                        <?php duplicate_hidden_fields($formData); ?>
                        <button class="btn btn-admin-soft" type="submit">Quay lại chỉnh sửa</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
