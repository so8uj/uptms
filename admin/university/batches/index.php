<?php

require_once("../../includes/header.php");
require_once("../../includes/sidebar.php");

$pageType = page_action() == 'create' ? "Add Batch" : "Edit Batch";

$buttonText = page_action() == 'create' ? "Add" : "Update";

$pageHeader = "Batch Management";

$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Batch Management" => ""
];

$batchForm = [
    "name" => page_action() == 'create' ? "" : "65B",
    "code" => page_action() == 'create' ? "" : "CSE-65B",
    "session" => page_action() == 'create' ? "" : "2025-2026",
    "department_id" => page_action() == 'create' ? "" : 1,
    "description" => page_action() == 'create' ? "" : "Computer Science & Engineering Batch 65B",
    "status" => page_action() == 'create' ? 1 : 1,
];

?>

<div class="main-wrapper">

    <?php
    require_once("../../includes/dashboard_header.php");
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="row g-4">

        <!-- Batch Management -->
        <div class="col-12 col-lg-8">

            <div class="table-card-custom">

                <!-- Table Header -->
                <div class="table-header-control">

                    <div class="table-search-box">

                        <i class="bi bi-search table-search-icon"></i>

                        <input
                            type="text"
                            class="table-search-input"
                            id="batchSearch"
                            placeholder="Search batches..."
                        >

                    </div>

                    <div class="table-filter-group">

                        <!-- Department -->
                        <div class="dropdown">

                            <button
                                class="btn btn-light dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                            >
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
                                <th>Batch</th>
                                <th>Code</th>
                                <th>Session</th>
                                <th>Department</th>
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
                                            <i class="bi bi-people"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                65B
                                            </div>

                                            <div class="table-user-sub">
                                                CSE Batch 65B
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    CSE-65B
                                </td>

                                <td>
                                    2025-2026
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    42 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/batches/index.php?action=update') ?>"
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
                                            <i class="bi bi-people"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                65A
                                            </div>

                                            <div class="table-user-sub">
                                                CSE Batch 65A
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    CSE-65A
                                </td>

                                <td>
                                    2025-2026
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    38 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/batches/index.php?action=update') ?>"
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
                                            <i class="bi bi-people"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                64A
                                            </div>

                                            <div class="table-user-sub">
                                                CSE Batch 64A
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    CSE-64A
                                </td>

                                <td>
                                    2024-2025
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    45 Students
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/batches/index.php?action=update') ?>"
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
                                            <i class="bi bi-people"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                63B
                                            </div>

                                            <div class="table-user-sub">
                                                CSE Batch 63B
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    CSE-63B
                                </td>

                                <td>
                                    2023-2024
                                </td>

                                <td>
                                    Computer Science & Engineering
                                </td>

                                <td>
                                    40 Students
                                </td>

                                <td>

                                    <span class="badge-table failed">
                                        Inactive
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/university/batches/index.php?action=update') ?>"
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
                        Showing 1 to 4 of 12 batches
                    </span>

                    <nav aria-label="Batch pagination">

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

        <!-- Add Batch -->
        <div class="col-12 col-lg-4">

            <div class="card border-light shadow-sm p-4">

                <h5 class="card-title mb-4">
                    <?= $buttonText ?> Batch
                </h5>

                <form action="" method="post">

                    <!-- Batch Name -->
                    <div class="mb-3">

                        <label for="batchName" class="form-label-custom">
                            Batch Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control-custom"
                            id="batchName"
                            name="name"
                            placeholder="Enter batch name"
                            value="<?= $batchForm['name'] ?>"
                            required
                        >

                    </div>

                    <!-- Batch Code -->
                    <div class="mb-3">

                        <label for="batchCode" class="form-label-custom">
                            Batch Code <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control-custom"
                            id="batchCode"
                            name="code"
                            placeholder="Enter batch code"
                            value="<?= $batchForm['code'] ?>"
                            required
                        >

                    </div>

                    <!-- Academic Session -->
                    <div class="mb-3">

                        <label for="batchSession" class="form-label-custom">
                            Academic Session <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control-custom"
                            id="batchSession"
                            name="session"
                            placeholder="Example: 2025-2026"
                            value="<?= $batchForm['session'] ?>"
                            required
                        >

                    </div>

                    <!-- Department -->
                    <div class="mb-3">

                        <label for="departmentId" class="form-label-custom">
                            Department <span class="text-danger">*</span>
                        </label>

                        <select
                            class="form-select-custom"
                            id="departmentId"
                            name="department_id"
                            required
                        >

                            <option value="">
                                Select Department
                            </option>

                            <option
                                value="1"
                                <?= $batchForm['department_id'] == 1 ? 'selected' : '' ?>
                            >
                                Computer Science & Engineering
                            </option>

                            <option
                                value="2"
                                <?= $batchForm['department_id'] == 2 ? 'selected' : '' ?>
                            >
                                Electrical & Electronic Engineering
                            </option>

                            <option
                                value="3"
                                <?= $batchForm['department_id'] == 3 ? 'selected' : '' ?>
                            >
                                Information Technology
                            </option>

                            <option
                                value="4"
                                <?= $batchForm['department_id'] == 4 ? 'selected' : '' ?>
                            >
                                Business Administration
                            </option>

                        </select>

                    </div>

                    <!-- Description -->
                    <div class="mb-3">

                        <label for="batchDescription" class="form-label-custom">
                            Description
                        </label>

                        <textarea
                            class="form-control-custom"
                            id="batchDescription"
                            name="description"
                            rows="3"
                            placeholder="Enter a short description..."
                        ><?= $batchForm['description'] ?></textarea>

                    </div>

                    <!-- Status -->
                    <div class="mb-4">

                        <label for="batchStatus" class="form-label-custom">
                            Status
                        </label>

                        <select
                            class="form-select-custom"
                            id="batchStatus"
                            name="status"
                        >

                            <option
                                value="1"
                                <?= $batchForm['status'] == 1 ? 'selected' : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                <?= $batchForm['status'] == 0 ? 'selected' : '' ?>
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

                            <?= $buttonText ?> Batch

                        </button>

                        <?php if ($buttonText == 'Update'): ?>

                            <a
                                href="<?= url('admin/university/batches/index.php?action=create') ?>"
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

<?php require_once("../../includes/footer.php"); ?>