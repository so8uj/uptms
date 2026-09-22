<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Thesis Evaluation";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Evaluation" => "",
    "Thesis Evaluation" => ""
];

$thesisEvaluations = [
    ["id" => "TH-EVAL-2026-021", "title" => "AI Based Student Advising Platform", "author" => "Nusrat Jahan", "department" => "Computer Science & Engineering", "reviewer" => "Dr. Mahmud Hasan", "score" => "88/100", "status" => "completed"],
    ["id" => "TH-EVAL-2026-019", "title" => "Digital Archive for University Research", "author" => "Tanvir Hasan", "department" => "Information Technology", "reviewer" => "Not assigned", "score" => "-", "status" => "pending"],
    ["id" => "TH-EVAL-2025-044", "title" => "Secure IoT Monitoring Network", "author" => "Fahim Ahmed", "department" => "Electrical & Electronic Engineering", "reviewer" => "Dr. Nasir Uddin", "score" => "92/100", "status" => "completed"],
];
?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="table-card-custom">
        <div class="table-header-control"><div class="row g-3"><div class="col-12 col-md-6"><div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search thesis title, author, or ID..."></div></div><div class="col-12 col-md-3"><select class="form-select-custom"><option value="">All Departments</option><option>Computer Science & Engineering</option><option>Information Technology</option><option>Electrical & Electronic Engineering</option></select></div><div class="col-12 col-md-3"><select class="form-select-custom"><option value="">All Status</option><option value="completed">Completed</option><option value="pending">Pending</option></select></div></div></div>

        <div class="table-responsive"><table class="table-custom"><thead><tr><th>Thesis</th><th>Author</th><th>Department</th><th>Reviewer</th><th>Score</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>
            <?php foreach ($thesisEvaluations as $evaluation): ?><tr><td><div class="table-user-name"><?= htmlspecialchars($evaluation['title']) ?></div><div class="table-user-sub"><?= htmlspecialchars($evaluation['id']) ?></div></td><td><?= htmlspecialchars($evaluation['author']) ?></td><td><?= htmlspecialchars($evaluation['department']) ?></td><td><?= htmlspecialchars($evaluation['reviewer']) ?></td><td><strong><?= htmlspecialchars($evaluation['score']) ?></strong></td><td><?php if ($evaluation['status'] === 'completed'): ?><span class="badge-table success">Completed</span><?php else: ?><span class="badge-table pending">Pending</span><?php endif; ?></td><td><div class="d-flex justify-content-center gap-1"><a href="#" class="table-btn-action" title="View thesis evaluation" aria-label="View thesis evaluation"><i class="bi bi-eye"></i></a><a href="#" class="table-btn-action" title="Edit thesis evaluation" aria-label="Edit thesis evaluation"><i class="bi bi-pencil"></i></a></div></td></tr><?php endforeach; ?>
        </tbody></table></div>

        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($thesisEvaluations) ?> of <?= count($thesisEvaluations) ?> thesis evaluations</div><nav aria-label="Thesis evaluation pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
