<?php
$users = $users ?? [];
$filters = $filters ?? ['keyword' => '', 'role' => '', 'is_verified' => '', 'is_active' => ''];
$successMessage = $_SESSION['admin_success'] ?? '';
$errorMessage = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

function e_admin_user($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function user_status_badge($value, $activeLabel, $inactiveLabel) {
    return ((int) $value === 1)
        ? '<span class="badge text-bg-success">' . e_admin_user($activeLabel) . '</span>'
        : '<span class="badge text-bg-secondary">' . e_admin_user($inactiveLabel) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-email { font-weight: 850; color: #101828; }
        .filter-grid { display:grid; grid-template-columns: minmax(220px, 1.4fr) repeat(3, minmax(150px, .8fr)) auto; gap:12px; align-items:end; }
        .table-actions { display:flex; justify-content:flex-end; gap:8px; }
        .icon-button { width:38px; height:38px; display:inline-grid; place-items:center; border-radius:8px; }
        @media (max-width: 991px) { .filter-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 575px) { .filter-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="admin-page-head">
            <div>
                <div class="admin-page-kicker">Accounts</div>
                <h1 class="admin-page-title">Quản lý người dùng</h1>
                <p class="admin-page-desc">Theo dõi tài khoản, phân quyền admin/user và vô hiệu hóa tài khoản khi cần.</p>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo e_admin_user($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?php echo e_admin_user($errorMessage); ?></div>
        <?php endif; ?>

        <section class="admin-card p-3 mb-3">
            <form method="GET" action="index.php" class="filter-grid">
                <input type="hidden" name="role" value="admin">
                <input type="hidden" name="controller" value="adminUser">
                <input type="hidden" name="action" value="index">
                <div>
                    <label class="form-label fw-bold">Tìm kiếm</label>
                    <input type="search" name="keyword" class="form-control" value="<?php echo e_admin_user($filters['keyword'] ?? ''); ?>" placeholder="Email hoặc họ tên">
                </div>
                <div>
                    <label class="form-label fw-bold">Role</label>
                    <select name="role_filter" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="user" <?php echo (($filters['role'] ?? '') === 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo (($filters['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="form-label fw-bold">Xác thực</label>
                    <select name="is_verified" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" <?php echo (($filters['is_verified'] ?? '') === '1') ? 'selected' : ''; ?>>Đã xác thực</option>
                        <option value="0" <?php echo (($filters['is_verified'] ?? '') === '0') ? 'selected' : ''; ?>>Chưa xác thực</option>
                    </select>
                </div>
                <div>
                    <label class="form-label fw-bold">Hoạt động</label>
                    <select name="is_active" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" <?php echo (($filters['is_active'] ?? '') === '1') ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="0" <?php echo (($filters['is_active'] ?? '') === '0') ? 'selected' : ''; ?>>Đã vô hiệu hóa</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-admin-primary px-4">Lọc</button>
                    <a href="index.php?role=admin&controller=adminUser&action=index" class="btn btn-admin-soft px-3">Xóa lọc</a>
                </div>
            </form>
        </section>

        <section class="admin-card">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:80px;">ID</th>
                            <th>Người dùng</th>
                            <th>Role</th>
                            <th>Xác thực</th>
                            <th>Hoạt động</th>
                            <th>Ngày tạo</th>
                            <th class="text-end pe-4" style="width:180px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <?php
                                    $isCurrentUser = (int) ($user['id'] ?? 0) === (int) ($_SESSION['user_id'] ?? 0);
                                    $nextActive = (int) ($user['is_active'] ?? 1) === 1 ? 0 : 1;
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?php echo (int) $user['id']; ?></td>
                                    <td>
                                        <div class="user-email"><?php echo e_admin_user($user['email']); ?></div>
                                        <div class="text-muted small"><?php echo e_admin_user($user['full_name'] ?: 'Chưa cập nhật họ tên'); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'text-bg-primary' : 'text-bg-light border'; ?>">
                                            <?php echo e_admin_user($user['role']); ?>
                                        </span>
                                        <?php if ($isCurrentUser): ?>
                                            <span class="badge text-bg-warning ms-1">Bạn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo user_status_badge($user['is_verified'], 'Đã xác thực', 'Chưa xác thực'); ?></td>
                                    <td><?php echo user_status_badge($user['is_active'], 'Đang hoạt động', 'Đã vô hiệu hóa'); ?></td>
                                    <td class="text-muted"><?php echo !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Chưa rõ'; ?></td>
                                    <td class="pe-4">
                                        <div class="table-actions">
                                            <button type="button"
                                                    class="btn btn-outline-primary icon-button"
                                                    data-edit-user
                                                    data-id="<?php echo (int) $user['id']; ?>"
                                                    data-email="<?php echo e_admin_user($user['email']); ?>"
                                                    data-full-name="<?php echo e_admin_user($user['full_name']); ?>"
                                                    data-role="<?php echo e_admin_user($user['role']); ?>"
                                                    data-is-verified="<?php echo (int) $user['is_verified']; ?>"
                                                    data-is-active="<?php echo (int) $user['is_active']; ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal"
                                                    title="Sửa người dùng">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form action="index.php?role=admin&controller=adminUser&action=toggleActive" method="POST" class="d-inline"
                                                  onsubmit="return confirm('<?php echo $nextActive ? 'Kích hoạt tài khoản này?' : 'Vô hiệu hóa tài khoản này?'; ?>');">
                                                <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $nextActive; ?>">
                                                <button type="submit"
                                                        class="btn <?php echo $nextActive ? 'btn-outline-success' : 'btn-outline-warning'; ?> icon-button"
                                                        title="<?php echo $nextActive ? 'Kích hoạt' : 'Vô hiệu hóa'; ?>">
                                                    <i class="fas <?php echo $nextActive ? 'fa-user-check' : 'fa-user-slash'; ?>"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                    Không tìm thấy người dùng phù hợp.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php?role=admin&controller=adminUser&action=update" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" id="edit_user_email" class="form-control" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Họ tên</label>
                    <input type="text" name="full_name" id="edit_user_full_name" class="form-control" placeholder="Nhập họ tên">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Role</label>
                    <select name="role" id="edit_user_role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Xác thực email</label>
                        <select name="is_verified" id="edit_user_verified" class="form-select">
                            <option value="1">Đã xác thực</option>
                            <option value="0">Chưa xác thực</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <select name="is_active" id="edit_user_active" class="form-select">
                            <option value="1">Đang hoạt động</option>
                            <option value="0">Vô hiệu hóa</option>
                        </select>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0 small">
                    Không thể tự hạ quyền, tự vô hiệu hóa, hoặc vô hiệu hóa admin cuối cùng.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-admin-primary w-100">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('[data-edit-user]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('edit_user_id').value = button.dataset.id || '';
        document.getElementById('edit_user_email').value = button.dataset.email || '';
        document.getElementById('edit_user_full_name').value = button.dataset.fullName || '';
        document.getElementById('edit_user_role').value = button.dataset.role || 'user';
        document.getElementById('edit_user_verified').value = button.dataset.isVerified || '0';
        document.getElementById('edit_user_active').value = button.dataset.isActive || '1';
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
