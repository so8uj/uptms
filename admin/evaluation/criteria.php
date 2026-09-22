<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Evaluation Criteria";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Evaluation" => "",
    "Evaluation Criteria" => ""
];

$criteria = [
    ["name" => "Research Quality", "category" => "Project Evaluation", "weight" => "25%", "description" => "Originality, relevance, and quality of the research work.", "status" => "active"],
    ["name" => "Technical Implementation", "category" => "Project Evaluation", "weight" => "30%", "description" => "Quality, correctness, and completeness of the implementation.", "status" => "active"],
    ["name" => "Documentation", "category" => "Thesis Evaluation", "weight" => "20%", "description" => "Structure, clarity, references, and academic presentation.", "status" => "active"],
    ["name" => "Presentation and Defense", "category" => "Thesis Evaluation", "weight" => "25%", "description" => "Communication, demonstration, and response to evaluation questions.", "status" => "inactive"],
];
?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="table-card-custom">
        <div class="table-header-control"><div class="row g-3"><div class="col-12 col-md-7"><div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text" class="table-search-input" placeholder="Search evaluation criteria..."></div></div><div class="col-12 col-md-3"><select class="form-select-custom"><option value="">All Categories</option><option>Project Evaluation</option><option>Thesis Evaluation</option></select></div><div class="col-12 col-md-2"><select class="form-select-custom"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div></div></div>

        <div class="table-responsive"><table class="table-custom"><thead><tr><th>Criteria</th><th>Category</th><th>Weight</th><th>Description</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>
            <?php foreach ($criteria as $item): ?><tr><td><div class="table-user-name"><?= htmlspecialchars($item['name']) ?></div></td><td><?= htmlspecialchars($item['category']) ?></td><td><strong><?= htmlspecialchars($item['weight']) ?></strong></td><td><?= htmlspecialchars($item['description']) ?></td><td><?php if ($item['status'] === 'active'): ?><span class="badge-table success">Active</span><?php else: ?><span class="badge-table pending">Inactive</span><?php endif; ?></td><td><div class="d-flex justify-content-center gap-1"><a href="#" class="table-btn-action" title="Edit criteria" aria-label="Edit criteria"><i class="bi bi-pencil"></i></a><button type="button" class="table-btn-action delete" title="Delete criteria" aria-label="Delete criteria"><i class="bi bi-trash"></i></button></div></td></tr><?php endforeach; ?>
        </tbody></table></div>

        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($criteria) ?> of <?= count($criteria) ?> criteria</div><nav aria-label="Criteria pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
