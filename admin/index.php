<?php 
require_once("./includes/header.php"); 
require_once("./includes/sidebar.php"); 


$pageHeader = "Dashboard";

?>

<div class="main-wrapper">
    <?php  
      require_once("./includes/dashboard_header.php"); 
      require_once(base_path('includes/admin/page_header_breadcrumb.php')); 

   
   ?>



    <!-- Dashboard Overview -->
    <div class="row g-4 mb-4">

        <!-- Total Students -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Total Students</p>
                            <h3 class="mb-1">1,248</h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i>
                                12% this year
                            </small>
                        </div>

                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Teachers -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Teachers / Supervisors</p>
                            <h3 class="mb-1">86</h3>
                            <small class="text-muted">
                                Academic faculty
                            </small>
                        </div>

                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="bi bi-person-workspace fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Departments</p>
                            <h3 class="mb-1">12</h3>
                            <small class="text-muted">
                                Active departments
                            </small>
                        </div>

                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batches -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Batches</p>
                            <h3 class="mb-1">24</h3>
                            <small class="text-muted">
                                Active batches
                            </small>
                        </div>

                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="bi bi-collection fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mb-4">

        <!-- Active Projects -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Active Projects</p>
                            <h3 class="mb-1">186</h3>
                            <small class="text-primary">
                                Currently in progress
                            </small>
                        </div>

                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-kanban fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Proposals -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Pending Proposals</p>
                            <h3 class="mb-1">28</h3>
                            <small class="text-warning">
                                Waiting for review
                            </small>
                        </div>

                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Submissions -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Pending Submissions</p>
                            <h3 class="mb-1">17</h3>
                            <small class="text-danger">
                                Requires attention
                            </small>
                        </div>

                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ongoing Research -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Ongoing Research</p>
                            <h3 class="mb-1">42</h3>
                            <small class="text-success">
                                Active research
                            </small>
                        </div>

                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="bi bi-search fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <!-- Research Library -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Research Library</p>
                            <h3 class="mb-1">324</h3>
                            <small class="text-muted">
                                Total theses & research
                            </small>
                        </div>

                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="bi bi-journal-bookmark fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Published Research -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Published Research</p>
                            <h3 class="mb-1">286</h3>
                            <small class="text-success">
                                Available in library
                            </small>
                        </div>

                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="bi bi-journal-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Research Review -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Research Review</p>
                            <h3 class="mb-1">11</h3>
                            <small class="text-warning">
                                Awaiting review
                            </small>
                        </div>

                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="bi bi-journal-arrow-up fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Projects -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2">Completed Projects</p>
                            <h3 class="mb-1">512</h3>
                            <small class="text-muted">
                                Successfully completed
                            </small>
                        </div>

                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>




</div>

<?php require_once("./includes/footer.php"); ?>