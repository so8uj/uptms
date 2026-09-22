<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Milestones";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "Project Management" => "", "Milestones" => ""];

$milestones = [
    ["name" => "Proposal Approval", "project" => "University Thesis Management System", "due" => "Aug 30, 2026", "completion" => 100, "status" => "completed"],
    ["name" => "Requirement Analysis", "project" => "University Thesis Management System", "due" => "Sep 25, 2026", "completion" => 80, "status" => "ongoing"],
    ["name" => "System Design", "project" => "Smart Campus Resource Platform", "due" => "Oct 08, 2026", "completion" => 35, "status" => "ongoing"],
    ["name" => "Final Submission", "project" => "Secure IoT Monitoring Network", "due" => "Jul 20, 2026", "completion" => 100, "status" => "completed"],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control"><div class="row g-3"><div class="col-12 col-md-7"><div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search milestone or project..."></div></div><div class="col-12 col-md-5"><select class="form-select-custom"><option value="">All Status</option><option>Ongoing</option><option>Completed</option><option>Overdue</option></select></div></div></div>
        <div class="table-responsive"><table class="table-custom"><thead><tr><th>Milestone</th><th>Project</th><th>Due Date</th><th>Completion</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
            <?php foreach ($milestones as $milestone): ?><tr><td><div class="table-user-name"><?= htmlspecialchars($milestone['name']) ?></div></td><td><?= htmlspecialchars($milestone['project']) ?></td><td><?= htmlspecialchars($milestone['due']) ?></td><td style="min-width: 150px;"><div class="d-flex justify-content-between small mb-1"><span><?= $milestone['completion'] ?>%</span></div><div class="progress" style="height: 6px;"><div class="progress-bar bg-primary" role="progressbar" style="width: <?= $milestone['completion'] ?>%;" aria-valuenow="<?= $milestone['completion'] ?>" aria-valuemin="0" aria-valuemax="100"></div></div></td><td><?php if ($milestone['status'] === 'completed'): ?><span class="badge bg-success-subtle text-success">Completed</span><?php else: ?><span class="badge bg-primary-subtle text-primary">Ongoing</span><?php endif; ?></td><td class="text-end"><a href="#" class="table-btn-action" title="Edit milestone" aria-label="Edit milestone"><i class="bi bi-pencil"></i></a><button type="button" class="table-btn-action delete" title="Delete milestone" aria-label="Delete milestone"><i class="bi bi-trash"></i></button></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($milestones) ?> of <?= count($milestones) ?> milestones</div><nav aria-label="Milestone pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>
