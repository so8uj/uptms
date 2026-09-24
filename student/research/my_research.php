<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "My Research";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "My Research" => "",
];

$research = [
    ["title" => "AI-Based Student Performance Prediction System", "category" => "Artificial Intelligence", "year" => "2026", "status" => "Approved", "type" => "Thesis"],
    ["title" => "Smart Campus Management Using IoT", "category" => "Internet of Things", "year" => "2025", "status" => "Under Review", "type" => "Research Paper"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input class="table-search-input" placeholder="Search my research...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option>All Status</option>
                        <option>Approved</option>
                        <option>Under Review</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= url('student/research/manage.php?action=create') ?>" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Add Research
                    </a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($research as $item): ?>
                        <tr>
                            <td class="table-user-name"><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['type']) ?></td>
                            <td><?= htmlspecialchars($item['year']) ?></td>
                            <td>
                                <?php if ($item['status'] === 'Approved'): ?>
                                    <span class="badge-table success">Approved</span>
                                <?php else: ?>
                                    <span class="badge-table warning">Under Review</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="#" class="table-btn-action" title="View research"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>
