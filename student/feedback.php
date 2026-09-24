<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Feedback";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Feedback" => "",
];

$feedback = [
    ["from" => "Dr. Mahmud Hasan", "title" => "Progress Review", "date" => "Sep 22, 2026", "message" => "The requirement analysis is clear. Please focus more on the evaluation criteria and data flow diagram.", "type" => "primary"],
    ["from" => "Dr. Rezaul Karim", "title" => "Prototype Feedback", "date" => "Sep 14, 2026", "message" => "Your prototype is promising. Improve the interface consistency and include user authentication flow.", "type" => "success"],
    ["from" => "Dr. Tahmina Akter", "title" => "Proposal Revision", "date" => "Sep 08, 2026", "message" => "The project concept is good, but the problem statement should be sharpened and the novelty explained better.", "type" => "warning"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="row g-4">
        <?php foreach ($feedback as $item): ?>
            <div class="col-12">
                <div class="card border-light shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-column flex-md-row gap-2 mb-3">
                            <div>
                                <div class="small text-muted mb-1"><?= htmlspecialchars($item['from']) ?></div>
                                <h5 class="mb-0"><?= htmlspecialchars($item['title']) ?></h5>
                            </div>
                            <span class="badge-table <?= htmlspecialchars($item['type']) ?>"><?= htmlspecialchars($item['date']) ?></span>
                        </div>
                        <p class="text-muted mb-0"><?= htmlspecialchars($item['message']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
