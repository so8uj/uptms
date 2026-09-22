<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Documents";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Project Files" => "",
    "Documents" => ""
];

$documents = [
    ["id" => "DOC-2026-041", "name" => "Thesis Management System - Final Report.pdf", "project" => "University Thesis Management System", "team" => "Team Alpha", "type" => "Final Report", "uploaded" => "Sep 20, 2026", "size" => "4.8 MB", "status" => "approved"],
    ["id" => "DOC-2026-040", "name" => "Smart Campus - System Design.docx", "project" => "Smart Campus Resource Platform", "team" => "Team Nova", "type" => "Design Document", "uploaded" => "Sep 19, 2026", "size" => "2.1 MB", "status" => "approved"],
    ["id" => "DOC-2026-039", "name" => "Secure IoT - Progress Report.pdf", "project" => "Secure IoT Monitoring Network", "team" => "Team Vertex", "type" => "Progress Report", "uploaded" => "Sep 17, 2026", "size" => "1.6 MB", "status" => "approved"],
    ["id" => "DOC-2026-038", "name" => "Crop Forecasting - Dataset Notes.pdf", "project" => "Machine Learning Based Crop Forecasting", "team" => "Team Insight", "type" => "Supporting File", "uploaded" => "Sep 16, 2026", "size" => "860 KB", "status" => "review"],
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
                <div class="col-12 col-md-5">
                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input type="text" class="table-search-input" placeholder="Search document, project, or team...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Document Types</option>
                        <option>Proposal</option>
                        <option>Design Document</option>
                        <option>Progress Report</option>
                        <option>Final Report</option>
                        <option>Supporting File</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Projects</option>
                        <option>University Thesis Management System</option>
                        <option>Smart Campus Resource Platform</option>
                        <option>Secure IoT Monitoring Network</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="review">Under Review</option>
                        <option value="rejected">Rejected</option>
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
                        <th>Uploaded</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td>
                                <div class="table-user-cell">
                                    <div class="table-btn-action"><i class="bi bi-file-earmark-text"></i></div>
                                    <div>
                                        <div class="table-user-name"><?= htmlspecialchars($document['name']) ?></div>
                                        <div class="table-user-sub"><?= htmlspecialchars($document['id']) ?> · <?= htmlspecialchars($document['team']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($document['project']) ?></td>
                            <td><?= htmlspecialchars($document['type']) ?></td>
                            <td><?= htmlspecialchars($document['uploaded']) ?></td>
                            <td><?= htmlspecialchars($document['size']) ?></td>
                            <td>
                                <?php if ($document['status'] === 'approved'): ?>
                                    <span class="badge-table success">Approved</span>
                                <?php else: ?>
                                    <span class="badge-table pending">Under Review</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="table-btn-action" title="View document" aria-label="View document"><i class="bi bi-eye"></i></a>
                                    <a href="#" class="table-btn-action" title="Download document" aria-label="Download document"><i class="bi bi-download"></i></a>
                                    <button type="button" class="table-btn-action delete" title="Delete document" aria-label="Delete document"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($documents) ?> of <?= count($documents) ?> documents</div>
            <nav aria-label="Document pagination">
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
