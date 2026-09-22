<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Progress";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "Project Management" => "", "Project Progress" => ""];

$progressUpdates = [
    ["project" => "University Thesis Management System", "team" => "Team Alpha", "update" => "Completed dashboard and student-management interface.", "progress" => 72, "updated" => "Sep 20, 2026", "status" => "on_track"],
    ["project" => "Smart Campus Resource Platform", "team" => "Team Nova", "update" => "Completed API requirements and initial data model.", "progress" => 48, "updated" => "Sep 19, 2026", "status" => "on_track"],
    ["project" => "Machine Learning Based Crop Forecasting", "team" => "Team Insight", "update" => "Dataset preparation is awaiting supervisor feedback.", "progress" => 12, "updated" => "Sep 16, 2026", "status" => "attention"],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control"><div class="row g-3"><div class="col-12 col-md-8"><div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search project, team, or update..."></div></div><div class="col-12 col-md-4"><select class="form-select-custom"><option value="">All Update Status</option><option value="on_track">On Track</option><option value="attention">Needs Attention</option></select></div></div></div>
        <div class="table-responsive"><table class="table-custom"><thead><tr><th>Project</th><th>Latest Update</th><th>Progress</th><th>Updated</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
            <?php foreach ($progressUpdates as $item): ?><tr><td><div class="table-user-name"><?= htmlspecialchars($item['project']) ?></div><div class="table-user-sub"><?= htmlspecialchars($item['team']) ?></div></td><td><?= htmlspecialchars($item['update']) ?></td><td style="min-width: 150px;"><div class="d-flex justify-content-between small mb-1"><span><?= $item['progress'] ?>%</span></div><div class="progress" style="height: 6px;"><div class="progress-bar bg-primary" role="progressbar" style="width: <?= $item['progress'] ?>%;" aria-valuenow="<?= $item['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div></div></td><td><?= htmlspecialchars($item['updated']) ?></td><td><?php if ($item['status'] === 'attention'): ?><span class="badge bg-warning-subtle text-warning">Needs Attention</span><?php else: ?><span class="badge bg-success-subtle text-success">On Track</span><?php endif; ?></td><td class="text-end"><a href="#" class="table-btn-action" title="View update" aria-label="View update"><i class="bi bi-eye"></i></a><a href="#" class="table-btn-action" title="Edit update" aria-label="Edit update"><i class="bi bi-pencil"></i></a></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($progressUpdates) ?> of <?= count($progressUpdates) ?> updates</div><nav aria-label="Progress pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>
