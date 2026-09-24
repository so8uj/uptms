<?php

require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Pending Submissions";

$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Pending Submissions" => ""
];

?>

<div class="main-wrapper">

    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="row g-4">

        <!-- Pending Submissions -->
        <div class="col-12">

            <div class="table-card-custom">

                <!-- Table Header -->
                <div class="table-header-control">

                    <div class="table-search-box">

                        <i class="bi bi-search table-search-icon"></i>

                        <input type="text" class="table-search-input" id="pendingSearch"
                            placeholder="Search submissions...">

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
                                        Research Paper
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Project
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
                                        Pending Review
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Under Review
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
                                <th>Submitted By</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Department</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <!-- Submission 1 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                AI-Based Student Performance Prediction System
                                            </div>

                                            <div class="table-user-sub">
                                                AI-Based Student Performance Prediction System
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Mohammad Sobuj
                                </td>

                                <td>
                                    Thesis
                                </td>

                                <td>
                                    Artificial Intelligence
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Sep 18, 2026
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        Pending Review
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/pending.php?action=view&id=1') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/pending.php?action=review&id=1') ?>"
                                            class="table-btn-action" title="Review">
                                            <i class="bi bi-check2-square"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Submission 2 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Smart Campus Management Using IoT
                                            </div>

                                            <div class="table-user-sub">
                                                Smart Campus Management Using IoT
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Nusrat Jahan
                                </td>

                                <td>
                                    Research Paper
                                </td>

                                <td>
                                    Internet of Things
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Sep 17, 2026
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        Pending Review
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/pending.php?action=view&id=2') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/pending.php?action=review&id=2') ?>"
                                            class="table-btn-action" title="Review">
                                            <i class="bi bi-check2-square"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Submission 3 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Blockchain-Based Academic Certificate Verification
                                            </div>

                                            <div class="table-user-sub">
                                                Blockchain-Based Academic Certificate Verification
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Tanvir Hasan
                                </td>

                                <td>
                                    Thesis
                                </td>

                                <td>
                                    Blockchain
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Sep 15, 2026
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        Under Review
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/pending.php?action=view&id=3') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/pending.php?action=review&id=3') ?>"
                                            class="table-btn-action" title="Review">
                                            <i class="bi bi-check2-square"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Submission 4 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Cyber Security Threat Detection Using Machine Learning
                                            </div>

                                            <div class="table-user-sub">
                                                Cyber Security Threat Detection Using Machine Learning
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Fahim Ahmed
                                </td>

                                <td>
                                    Research Paper
                                </td>

                                <td>
                                    Cyber Security
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    Sep 14, 2026
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        Pending Review
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/pending.php?action=view&id=4') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/pending.php?action=review&id=4') ?>"
                                            class="table-btn-action" title="Review">
                                            <i class="bi bi-check2-square"></i>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <!-- Submission 5 -->
                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Automated Medical Image Classification
                                            </div>

                                            <div class="table-user-sub">
                                                Automated Medical Image Classification
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    Sadia Akter
                                </td>

                                <td>
                                    Thesis
                                </td>

                                <td>
                                    Machine Learning
                                </td>

                                <td>
                                    Electrical & Electronic Engineering
                                </td>

                                <td>
                                    Sep 12, 2026
                                </td>

                                <td>

                                    <span class="badge-table pending">
                                        Under Review
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a href="<?= url('admin/research/pending.php?action=view&id=5') ?>"
                                            class="table-btn-action" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="<?= url('admin/research/pending.php?action=review&id=5') ?>"
                                            class="table-btn-action" title="Review">
                                            <i class="bi bi-check2-square"></i>
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
                        Showing 1 to 5 of 12 submissions
                    </span>

                    <nav aria-label="Pending submission pagination">

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