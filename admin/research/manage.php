<?php 

require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));


$pageType = page_action() == 'create' ? "Add Thesis/Research " : "Edit Thesis/Research ";
$pageHeader = $pageType;
$showBreadcrumb = true;
$breadcrumbs = [
   "Dashboard" => url('admin/index.php'),
   "Research Library" => url('admin/research/index.php'),
    $pageType => ""
];


?>

<div class="main-wrapper">
    <?php  

        require_once(base_path('admin/includes/dashboard_header.php'));
        require_once(base_path('includes/admin/page_header_breadcrumb.php')); 

        require_once(base_path('includes/research/research_form.php'));
    ?>


</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>