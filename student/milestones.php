<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Milestones";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Milestones" => "",
];

$milestones = [
    ["title" => "Project Proposal Submission", "due" => "Sep 12, 2026", "status" => "completed"],
    ["title" => "Requirement Analysis", "due" => "Sep 28, 2026", "status" => "in_progress"],
    ["title" => "System Design Review", "due" => "Oct 10, 2026", "status" => "pending"],
    ["title" => "Prototype Demonstration", "due" => "Oct 22, 2026", "status" => "pending"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="card border-light shadow-sm">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Milestone</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($milestones as $milestone): ?>
                        <tr>
                            <td class="table-user-name"><?= htmlspecialchars($milestone['title']) ?></td>
                            <td><?= htmlspecialchars($milestone['due']) ?></td>
                            <td>
                                <?php if ($milestone['status'] === 'completed'): ?>
                                    <span class="badge-table success">Completed</span>
                                <?php elseif ($milestone['status'] === 'in_progress'): ?>
                                    <span class="badge-table warning">In Progress</span>
                                <?php else: ?>
                                    <span class="badge-table pending">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
