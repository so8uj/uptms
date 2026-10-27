<?php 
require_once(__DIR__ . '/../app/bootstrap.php');



require_once(base_path('admin/includes/header.php')); 
require_once(base_path('admin/includes/sidebar.php')); 

$pageHeader = "Dashboard";

?>

<div class="main-wrapper">
    <?php  
        require_once(base_path('admin/includes/dashboard_header.php'));
        require_once(base_path('includes/admin/page_header_breadcrumb.php')); 
    ?>



    <div x-data="Sobuj_adminNotice('<?= url('api/menu_counts.php') ?>')" x-cloak x-show="newRequests > 0 || needSupervisor > 0 || completion > 0"
        class="alert alert-warning">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div><i class="bi bi-bell me-2"></i><strong x-text="message()"></strong></div>
            <div class="d-flex gap-2">
                <a href="<?= url('admin/research/pending.php') ?>" class="btn btn-sm btn-primary" x-show="newRequests > 0">Review requests</a>
                <a href="<?= url('admin/research/ongoing.php') ?>" class="btn btn-sm btn-primary" x-show="needSupervisor > 0">Assign supervisors</a>
                <a href="<?= url('admin/research/completion.php') ?>" class="btn btn-sm btn-primary" x-show="completion > 0">Complete research</a>
            </div>
        </div>
    </div>

    <?php
    // One card: the label, the number (a key of api/dashboard.php), a short line, the icon, the colour and the page it opens.
    $cards = [
        ["Students", "students", "Active students", "bi-people", "primary", "admin/university/students/index.php"],
        ["Teachers / Supervisors", "teachers", "Active teachers", "bi-person-workspace", "success", "admin/university/teachers/index.php"],
        ["Departments", "departments", "Active departments", "bi-building", "info", "admin/university/departments/index.php"],
        ["Batches", "batches", "Active batches", "bi-collection", "warning", "admin/university/batches/index.php"],

        ["New Requests", "new_requests", "Waiting for review", "bi-inbox", "danger", "admin/research/pending.php"],
        ["Need a Supervisor", "need_supervisor", "Approved, no supervisor yet", "bi-person-exclamation", "warning", "admin/research/ongoing.php"],
        ["Ongoing Research", "ongoing", "Supervisor assigned", "bi-activity", "success", "admin/research/ongoing.php"],
        ["Completed Research", "completed", "Finished research", "bi-check2-circle", "primary", "admin/research/index.php"],

        ["Milestones", "milestones", "Created in all projects", "bi-flag", "primary", "admin/research/ongoing.php"],
        ["Open Tasks", "open_tasks", "Still to finish", "bi-list-check", "warning", "admin/research/ongoing.php"],
        ["Finished Tasks", "done_tasks", "Done by teams and supervisors", "bi-check2-square", "success", "admin/research/ongoing.php"],
        ["Evaluations", "evaluations", "Written by supervisors", "bi-clipboard-check", "info", "admin/research/ongoing.php"],
    ];
    ?>

    <div x-data="Sobuj_adminDashboard('<?= url('api/dashboard.php') ?>')" class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">

        <?php foreach ($cards as [$label, $key, $detail, $icon, $color, $page]): ?>
            <div class="col">
                <a href="<?= url($page) ?>" class="text-decoration-none" title="<?= htmlspecialchars($detail) ?>">
                    <div class="card border-light border-start border-3 shadow-sm h-100 p-0 mb-0 rounded-3" style="border-left-color: var(--bs-<?= $color ?>) !important;">
                        <div class="card-body d-flex justify-content-between align-items-center gap-2 py-2 px-3">
                            <span class="d-flex align-items-center gap-2 overflow-hidden">
                                <i class="bi <?= $icon ?> text-<?= $color ?>"></i>
                                <span class="small text-muted text-truncate"><?= htmlspecialchars($label) ?></span>
                            </span>
                            <span class="fs-5 fw-bold text-dark lh-1" x-text="value('<?= $key ?>')">0</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

    </div>

</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
