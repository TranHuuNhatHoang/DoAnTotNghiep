<?php
$products = $products ?? [];
$platformStats = $platformStats ?? [];
$productPlatformMap = $productPlatformMap ?? [];

function e_admin_platform_overview($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_platform_overview_date($value) {
    return !empty($value) ? date('H:i d/m/Y', strtotime($value)) : 'Chưa quét';
}

function admin_platform_chip_class($state) {
    if ($state === null) {
        return 'missing';
    }

    return ((int) $state === 1) ? 'active' : 'paused';
}

function admin_platform_chip_label($state) {
    if ($state === null) {
        return 'Chưa gắn';
    }

    return ((int) $state === 1) ? 'Đang bật' : 'Tạm tắt';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan link sàn - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            padding: 18px;
        }

        .stat-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .stat-logo {
            height: 30px;
            max-width: 110px;
            object-fit: contain;
        }

        .stat-number {
            color: #101828;
            font-size: 1.75rem;
            font-weight: 950;
            line-height: 1;
        }

        .stat-label {
            color: #667085;
            font-size: .84rem;
            font-weight: 800;
        }

        .platform-thumb {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            background: #fff;
            display: grid;
            place-items: center;
            overflow: hidden;
            flex: 0 0 auto;
        }

        .platform-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .platform-thumb i {
            color: #cbd5e1;
        }

        .platform-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .platform-chip {
            min-width: 116px;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 8px;
            border: 1px solid #e4e7ec;
            font-size: .78rem;
            font-weight: 850;
        }

        .platform-chip.active {
            background: #ecfdf3;
            border-color: #bbf7d0;
            color: #166534;
        }

        .platform-chip.paused {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .platform-chip.missing {
            background: #f8fafc;
            color: #667085;
        }

        .icon-button {
            width: 40px;
            height: 40px;
            display: inline-grid;
            place-items: center;
            border-radius: 8px;
        }

        @media (max-width: 1199px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
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
                <h1 class="admin-page-title">Tổng quan link sàn</h1>
                <p class="admin-page-desc">Theo dõi trạng thái gắn link Tiki, Shopee và Lazada cho từng sản phẩm.</p>
            </div>
            <a href="index.php?role=admin&controller=adminProduct&action=index" class="btn btn-admin-primary px-4">
                <i class="fas fa-box me-2"></i>Danh sách sản phẩm
            </a>
        </div>

        <section class="stat-grid">
            <?php
            $platformLogos = [
                'Tiki' => 'https://upload.wikimedia.org/wikipedia/commons/4/43/Logo_Tiki_2023.png',
                'Shopee' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg',
                'Lazada' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Lazada_logo.svg/2560px-Lazada_logo.svg.png',
            ];
            ?>
            <?php foreach (['Tiki', 'Shopee', 'Lazada'] as $platform): ?>
                <?php $stat = $platformStats[$platform] ?? ['total_links' => 0, 'active_links' => 0, 'last_scraped_at' => null]; ?>
                <article class="admin-card stat-card">
                    <div class="stat-head">
                        <img class="stat-logo" src="<?php echo e_admin_platform_overview($platformLogos[$platform]); ?>" alt="<?php echo e_admin_platform_overview($platform); ?>">
                        <span class="badge text-bg-light border"><?php echo e_admin_platform_overview(admin_platform_overview_date($stat['last_scraped_at'] ?? null)); ?></span>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-number"><?php echo (int) ($stat['active_links'] ?? 0); ?></div>
                            <div class="stat-label">Link đang bật</div>
                        </div>
                        <div class="col-6">
                            <div class="stat-number"><?php echo (int) ($stat['total_links'] ?? 0); ?></div>
                            <div class="stat-label">Tổng link</div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 82px;">ID</th>
                            <th>Sản phẩm</th>
                            <th>Trạng thái link sàn</th>
                            <th>Cập nhật cuối</th>
                            <th class="text-center pe-4" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <?php
                                $productId = (int) $product['id'];
                                $platformMap = $productPlatformMap[$productId] ?? [];
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $productId; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="platform-thumb">
                                                <?php if (!empty($product['thumbnail_url'])): ?>
                                                    <img src="<?php echo e_admin_platform_overview($product['thumbnail_url']); ?>" alt="<?php echo e_admin_platform_overview($product['name']); ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-box-open"></i>
                                                <?php endif; ?>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="fw-bold line-clamp-2" title="<?php echo e_admin_platform_overview($product['name']); ?>">
                                                    <?php echo e_admin_platform_overview($product['name']); ?>
                                                </span>
                                                <span class="text-muted small">
                                                    <?php echo !empty($product['category_name']) ? e_admin_platform_overview($product['category_name']) : 'Chưa phân loại'; ?>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="platform-chips">
                                            <?php
                                            $chipStates = [
                                                'Tiki' => $platformMap['tiki_active'] ?? null,
                                                'Shopee' => $platformMap['shopee_active'] ?? null,
                                                'Lazada' => $platformMap['lazada_active'] ?? null,
                                            ];
                                            ?>
                                            <?php foreach ($chipStates as $platform => $state): ?>
                                                <span class="platform-chip <?php echo e_admin_platform_overview(admin_platform_chip_class($state)); ?>">
                                                    <span><?php echo e_admin_platform_overview($platform); ?></span>
                                                    <span><?php echo e_admin_platform_overview(admin_platform_chip_label($state)); ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo e_admin_platform_overview(admin_platform_overview_date($product['last_update'] ?? null)); ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="index.php?role=admin&controller=adminPlatform&action=index&product_id=<?php echo $productId; ?>"
                                           class="btn btn-outline-primary icon-button"
                                           title="Quản lý link sàn">
                                            <i class="fas fa-link"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
                                    Chưa có sản phẩm nào để gắn link sàn.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
