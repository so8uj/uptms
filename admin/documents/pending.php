<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Pending Documents";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Project Files" => "",
    "Documents" => url('admin/documents/index.php'),
    "Pending Documents" => ""
];

$pendingDocuments = [
    ["id" => "DOC-2026-038", "name" => "Crop Forecasting - Dataset Notes.pdf", "project" => "Machine Learning Based Crop Forecasting", "team" => "Team Insight", "type" => "Supporting File", "submitted" => "Sep 16, 2026", "reviewer" => "Dr. Mahmud Hasan", "priority" => "high"],
    ["id" => "DOC-2026-035", "name" => "Campus Platform - Requirement Analysis.docx", "project" => "Smart Campus Resource Platform", "team" => "Team Nova", "type" => "Requirement Document", "submitted" => "Sep 13, 2026", "reviewer" => "Dr. Farhana Rahman", "priority" => "normal"],
    ["id" => "DOC-2026-031", "name" => "Thesis Management - Testing Report.pdf", "project" => "University Thesis Management System", "team" => "Team Alpha", "type" => "Testing Report", "submitted" => "Sep 10, 2026", "reviewer" => "Not assigned", "priority" => "normal"],
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
                <div class="col-12 col-md-7">
                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input type="text" class="table-search-input" placeholder="Search pending document or project...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Reviewers</option>
                        <option>Dr. Mahmud Hasan</option>
                        <option>Dr. Farhana Rahman</option>
                        <option>Not assigned</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Priority</option>
                        <option value="high">High Priority</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Project</th>
                        <th>Type</th>
                        <th>Submitted</th>
                        <th>Reviewer</th>
                        <th>Priority</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingDocuments as $document): ?>
                        <tr>
                            <td>
                                <div class="table-user-name"><?= htmlspecialchars($document['name']) ?></div>
                                <div class="table-user-sub"><?= htmlspecialchars($document['id']) ?> · <?= htmlspecialchars($document['team']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($document['project']) ?></td>
                            <td><?= htmlspecialchars($document['type']) ?></td>
                            <td><?= htmlspecialchars($document['submitted']) ?></td>
                            <td><?= htmlspecialchars($document['reviewer']) ?></td>
                            <td>
                                <?php if ($document['priority'] === 'high'): ?>
                                    <span class="badge-table failed">High Priority</span>
                                <?php else: ?>
                                    <span class="badge-table pending">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="#" class="table-btn-action" title="Review document" aria-label="Review document"><i class="bi bi-clipboard-check"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($pendingDocuments) ?> of <?= count($pendingDocuments) ?> pending documents</div>
            <nav aria-label="Pending document pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
