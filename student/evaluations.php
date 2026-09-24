<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Evaluations";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Evaluations" => "",
];

$evaluations = [
    ["title" => "Proposal Evaluation", "examiner" => "Dr. Mahmud Hasan", "score" => "88/100", "status" => "completed"],
    ["title" => "Mid-Term Progress Review", "examiner" => "Dr. Rezaul Karim", "score" => "82/100", "status" => "completed"],
    ["title" => "Final Presentation", "examiner" => "Panel Members", "score" => "Pending", "status" => "pending"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="card border-light shadow-sm">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Evaluation</th>
                        <th>Examiner</th>
                        <th>Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $evaluation): ?>
                        <tr>
                            <td class="table-user-name"><?= htmlspecialchars($evaluation['title']) ?></td>
                            <td><?= htmlspecialchars($evaluation['examiner']) ?></td>
                            <td><?= htmlspecialchars($evaluation['score']) ?></td>
                            <td>
                                <?php if ($evaluation['status'] === 'completed'): ?>
                                    <span class="badge-table success">Completed</span>
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
