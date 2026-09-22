<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Admins";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Admins" => ""
];

$admins = [
    ["name" => "Mohammad Sobuj", "email" => "admin@uptms.edu", "department" => "Administration", "last_login" => "Sep 23, 2026", "status" => "active"],
    ["name" => "System Administrator", "email" => "system@uptms.edu", "department" => "IT Administration", "last_login" => "Sep 22, 2026", "status" => "active"],
];
?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-8"><div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search admin by name or email..."></div></div>
                <div class="col-12 col-md-4"><select class="form-select-custom"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            </div>
        </div>

        <div class="table-responsive"><table class="table-custom"><thead><tr><th>Admin</th><th>Department</th><th>Last Login</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>
            <?php foreach ($admins as $admin): ?><tr><td><div class="table-user-name"><?= htmlspecialchars($admin['name']) ?></div><div class="table-user-sub"><?= htmlspecialchars($admin['email']) ?></div></td><td><?= htmlspecialchars($admin['department']) ?></td><td><?= htmlspecialchars($admin['last_login']) ?></td><td><span class="badge-table success">Active</span></td><td><div class="d-flex justify-content-center gap-1"><a href="#" class="table-btn-action" title="View admin" aria-label="View admin"><i class="bi bi-eye"></i></a><a href="#" class="table-btn-action" title="Edit admin" aria-label="Edit admin"><i class="bi bi-pencil"></i></a></div></td></tr><?php endforeach; ?>
        </tbody></table></div>

        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($admins) ?> of <?= count($admins) ?> admins</div><nav aria-label="Admin pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
