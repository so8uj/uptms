<?php

require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Ongoing Research";

$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Ongoing Research" => ""
];

?>

<div class="main-wrapper">

    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="row g-4">

        <!-- Ongoing Research -->
        <div class="col-12">

            <div class="table-card-custom">

                <!-- Table Header -->
                <div class="table-header-control">

                    <div class="table-search-box">

                        <i class="bi bi-search table-search-icon"></i>

                        <input type="text" class="table-search-input" id="ongoingResearchSearch"
                            placeholder="Search ongoing research...">

                    </div>

                    <div class="table-filter-group">

                        <!-- Research Type -->
                        <div class="dropdown">

                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Research Type
                            </button>

                            <ul class="dropdown-menu">

                                <li>
                                    <a class="dropdown-item" href="#">
                                        All Types
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Thesis
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Research Project
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Research Paper
                                    </a>
                                </li>

                            </ul>

                        </div>

                        <!-- Department -->
                        <div class="dropdown">

                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Department
                            </button>

                            <ul class="dropdown-menu">

                                <li>
                                    <a class="dropdown-item" href="#">
                                        All Departments
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Computer Science & Engineering
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Electrical & Electronic Engineering
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Information Technology
                                    </a>
                                </li>

                            </ul>

                        </div>

                        <!-- Status -->
                        <div class="dropdown">

                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Status
                            </button>

                            <ul class="dropdown-menu">

                                <li>
                                    <a class="dropdown-item" href="#">
                                        All Status
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        In Progress
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        On Hold
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Completed
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>

                <!-- Table -->
                <div class="table-responsive">

                    <table class="table-custom">

                        <thead>

                            <tr>
                                <th>Research</th>
                                <th>Researchers</th>
                                <th>Supervisor</th>
                                <th>Category</th>
                                <th>Department</th>
                                <th>Start Date</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <!-- Research 1 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-flask"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                AI-Based Student Performance Prediction
                                            </div>

                                            <div class="table-user-sub">
                                                Research Project
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Mohammad Sobuj, Nusrat Jahan
                                </td>

                                <td>
                                    Dr. Rahman
                                </td>

                                <td>
                                    Artificial Intelligence
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Jan 10, 2026
                                </td>

                                <td>
                                    68%
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        In Progress
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/ongoing.php?action=view&id=1') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/ongoing.php?action=update&id=1') ?>"
                                            class="table-btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Research 2 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-flask"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Smart Campus IoT Monitoring System
                                            </div>

                                            <div class="table-user-sub">
                                                Research Project
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Nusrat Jahan, Fahim Ahmed
                                </td>

                                <td>
                                    Prof. Karim
                                </td>

                                <td>
                                    Internet of Things
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Feb 05, 2026
                                </td>

                                <td>
                                    52%
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        In Progress
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/ongoing.php?action=view&id=2') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/ongoing.php?action=update&id=2') ?>"
                                            class="table-btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Research 3 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-flask"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Blockchain Academic Certificate Verification
                                            </div>

                                            <div class="table-user-sub">
                                                Thesis
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Tanvir Hasan
                                </td>

                                <td>
                                    Dr. Ahmed
                                </td>

                                <td>
                                    Blockchain
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Mar 12, 2026
                                </td>

                                <td>
                                    41%
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        In Progress
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/ongoing.php?action=view&id=3') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/ongoing.php?action=update&id=3') ?>"
                                            class="table-btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Research 4 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-flask"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Cyber Threat Detection Using Machine Learning
                                            </div>

                                            <div class="table-user-sub">
                                                Research Project
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Fahim Ahmed, Sadia Akter
                                </td>

                                <td>
                                    Prof. Hasan
                                </td>

                                <td>
                                    Cyber Security
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Apr 20, 2026
                                </td>

                                <td>
                                    35%
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        In Progress
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/ongoing.php?action=view&id=4') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/ongoing.php?action=update&id=4') ?>"
                                            class="table-btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Research 5 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-flask"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Automated Medical Image Classification
                                            </div>

                                            <div class="table-user-sub">
                                                Research Project
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Sadia Akter
                                </td>

                                <td>
                                    Dr. Mahmud
                                </td>

                                <td>
                                    Machine Learning
                                </td>

                                <td>
                                    Electrical & Electronic Engineering
                                </td>

                                <td>
                                    May 15, 2026
                                </td>

                                <td>
                                    27%
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        In Progress
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/ongoing.php?action=view&id=5') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/ongoing.php?action=update&id=5') ?>"
                                            class="table-btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- Table Footer -->
                <div class="table-footer-control">

                    <span class="table-pagination-info">
                        Showing 1 to 5 of 16 research
                    </span>

                    <nav aria-label="Ongoing research pagination">

                        <ul class="pagination mb-0">

                            <li class="page-item disabled">
                                <a class="page-link" href="#">
                                    Previous
                                </a>
                            </li>

                            <li class="page-item active">
                                <a class="page-link" href="#">
                                    1
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link" href="#">
                                    2
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link" href="#">
                                    3
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link" href="#">
                                    Next
                                </a>
                            </li>

                        </ul>

                    </nav>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>