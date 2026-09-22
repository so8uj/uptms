<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Research Reports";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "Reports" => "", "Research Reports" => ""];

$reports = [
    ["title" => "AI-Based Student Performance Prediction System", "type" => "Thesis", "category" => "Artificial Intelligence", "author" => "Mohammad Sobuj", "year" => "2026", "status" => "Published"],
    ["title" => "Smart Campus Management Using IoT", "type" => "Research Paper", "category" => "Internet of Things", "author" => "Nusrat Jahan", "year" => "2025", "status" => "Published"],
    ["title" => "Secure Data Sharing for Academic Networks", "type" => "Project", "category" => "Cyber Security", "author" => "Tanvir Hasan", "year" => "2026", "status" => "Under Review"],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control"><div class="row g-3"><div class="col-12 col-md-6"><div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search research title or author..."></div></div><div class="col-12 col-md-3"><select class="form-select-custom"><option>All Types</option><option>Thesis</option><option>Research Paper</option><option>Project</option></select></div><div class="col-12 col-md-3"><select class="form-select-custom"><option>All Status</option><option>Published</option><option>Under Review</option></select></div></div></div>
        <div class="table-responsive"><table class="table-custom"><thead><tr><th>Research</th><th>Type</th><th>Category</th><th>Author</th><th>Year</th><th>Status</th><th class="text-center">Action</th></tr></thead><tbody>
            <?php foreach ($reports as $report): ?><tr><td><div class="table-user-name"><?= htmlspecialchars($report['title']) ?></div></td><td><?= htmlspecialchars($report['type']) ?></td><td><?= htmlspecialchars($report['category']) ?></td><td><?= htmlspecialchars($report['author']) ?></td><td><?= htmlspecialchars($report['year']) ?></td><td><?php if ($report['status'] === 'Published'): ?><span class="badge-table success">Published</span><?php else: ?><span class="badge-table pending">Under Review</span><?php endif; ?></td><td class="text-center"><a href="#" class="table-btn-action" title="View research report" aria-label="View research report"><i class="bi bi-eye"></i></a></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($reports) ?> of <?= count($reports) ?> research reports</div><nav aria-label="Research report pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>
