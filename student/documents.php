<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Documents";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Documents" => "",
];

$documents = [
    ["title" => "Requirement Analysis Report", "type" => "Report", "uploaded" => "Sep 12, 2026", "status" => "approved"],
    ["title" => "Project Proposal Draft", "type" => "Proposal", "uploaded" => "Sep 18, 2026", "status" => "pending"],
    ["title" => "System Design Diagram", "type" => "Design", "uploaded" => "Sep 24, 2026", "status" => "review"],
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
                        <input class="table-search-input" placeholder="Search document title...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option>All Type</option>
                        <option>Report</option>
                        <option>Proposal</option>
                        <option>Design</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <a href="#" class="btn btn-primary w-100"><i class="bi bi-upload me-1"></i>Upload</a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Uploaded</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td class="table-user-name"><?= htmlspecialchars($document['title']) ?></td>
                            <td><?= htmlspecialchars($document['type']) ?></td>
                            <td><?= htmlspecialchars($document['uploaded']) ?></td>
                            <td>
                                <?php if ($document['status'] === 'approved'): ?>
                                    <span class="badge-table success">Approved</span>
                                <?php elseif ($document['status'] === 'pending'): ?>
                                    <span class="badge-table pending">Pending</span>
                                <?php else: ?>
                                    <span class="badge-table warning">Under Review</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="#" class="table-btn-action" title="View document"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
