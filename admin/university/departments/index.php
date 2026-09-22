<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageType = page_action() == 'create' ? "Add Department" : "Edit Department";

$buttonText = page_action() == 'create' ? "Add" : "Update";

$pageHeader = "Department Management";

$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Department Management" => ""
];

$departmentForm = [
    "name" => page_action() == 'create' ? "" : "Computer Science & Engineering",
    "code" => page_action() == 'create' ? "" : "CSE",
    "description" => page_action() == 'create' ? "" : "Department of Computer Science and Engineering",
    "status" => page_action() == 'create' ? 1 : 1,
];

?>

<div class="main-wrapper">

    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="row g-4">

        <!-- Department Management -->
        <div class="col-12 col-lg-8">

            <div class="table-card-custom">

                <!-- Table Header -->
                <div class="table-header-control">

                    <div class="table-search-box">

                        <i class="bi bi-search table-search-icon"></i>

                        <input
                            type="text"
                            class="table-search-input"
                            id="departmentSearch"
                            placeholder="Search departments..."
                        >

                    </div>

                    <div class="table-filter-group">

                        <div class="dropdown">

                            <button
                                class="btn btn-light dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                            >
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
                                        Active
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        Inactive
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
                                <th>Department</th>
                                <th>Code</th>
                                <th>Students</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-building"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Computer Science & Engineering
                                            </div>

                                            <div class="table-user-sub">
                                                Department of Computer Science and Engineering
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    CSE
                                </td>

                                <td>
                                    245 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/departments/index.php?action=update') ?>"
                                            class="table-btn-action"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button
                                            type="button"
                                            class="table-btn-action delete"
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-building"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Electrical & Electronic Engineering
                                            </div>

                                            <div class="table-user-sub">
                                                Department of Electrical and Electronic Engineering
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    EEE
                                </td>

                                <td>
                                    198 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/departments/index.php?action=update') ?>"
                                            class="table-btn-action"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button
                                            type="button"
                                            class="table-btn-action delete"
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-building"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Information Technology
                                            </div>

                                            <div class="table-user-sub">
                                                Department of Information Technology
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    IT
                                </td>

                                <td>
                                    156 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/departments/index.php?action=update') ?>"
                                            class="table-btn-action"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button
                                            type="button"
                                            class="table-btn-action delete"
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-building"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Business Administration
                                            </div>

                                            <div class="table-user-sub">
                                                Department of Business Administration
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    BBA
                                </td>

                                <td>
                                    175 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/departments/index.php?action=update') ?>"
                                            class="table-btn-action"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <button
                                            type="button"
                                            class="table-btn-action delete"
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- Table Footer -->
                <div class="table-footer-control">

                    <span class="table-pagination-info">
                        Showing 1 to 4 of 8 departments
                    </span>

                    <nav aria-label="Department pagination">

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
                                    Next
                                </a>
                            </li>

                        </ul>

                    </nav>

                </div>

            </div>

        </div>

        <!-- Add Department -->
        <div class="col-12 col-lg-4">

            <div class="card border-light shadow-sm p-4">

                <h5 class="card-title mb-4">
                    <?= $buttonText ?> Department
                </h5>

                <form action="" method="post">

                    <!-- Department Name -->
                    <div class="mb-3">

                        <label for="departmentName" class="form-label-custom">
                            Department Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control-custom"
                            id="departmentName"
                            name="name"
                            placeholder="Enter department name"
                            value="<?= $departmentForm['name'] ?>"
                            required
                        >

                    </div>

                    <!-- Department Code -->
                    <div class="mb-3">

                        <label for="departmentCode" class="form-label-custom">
                            Department Code <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control-custom"
                            id="departmentCode"
                            name="code"
                            placeholder="Enter department code"
                            value="<?= $departmentForm['code'] ?>"
                            required
                        >

                    </div>

                    <!-- Description -->
                    <div class="mb-3">

                        <label for="departmentDescription" class="form-label-custom">
                            Description
                        </label>

                        <textarea
                            class="form-control-custom"
                            id="departmentDescription"
                            name="description"
                            rows="4"
                            placeholder="Enter a short description..."
                        ><?= $departmentForm['description'] ?></textarea>

                    </div>

                    <!-- Status -->
                    <div class="mb-4">

                        <label for="departmentStatus" class="form-label-custom">
                            Status
                        </label>

                        <select
                            class="form-select-custom"
                            id="departmentStatus"
                            name="status"
                        >

                            <option
                                value="1"
                                <?= $departmentForm['status'] == 1 ? 'selected' : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                <?= $departmentForm['status'] == 0 ? 'selected' : '' ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi <?= $buttonText == 'Add' ? 'bi-plus-circle' : 'bi-pencil' ?> me-1"></i>

                            <?= $buttonText ?> Department

                        </button>

                        <?php if ($buttonText == 'Update'): ?>

                            <a
                                href="<?= url('admin/university/departments/index.php?action=create') ?>"
                                class="btn btn-light"
                            >
                                Cancel
                            </a>

                        <?php endif; ?>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>