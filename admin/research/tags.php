<?php

require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageType = page_action() == 'create' ? "Add Research Tag" : "Edit Research Tag";

$buttonText = page_action() == 'create' ? "Add" : "Update";

$pageHeader = "Research Tag Management";

$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Research Tag Management" => ""
];

$tagForm = [
    "name" => page_action() == 'create' ? "" : "Machine learning",
    "description" => page_action() == 'create' ? "" : "Machine learning related research",
    "status" => page_action() == 'create' ? 1 : 1,
];

?>

<div class="main-wrapper">

    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="row g-4">

        <!-- Research Tag Management -->
        <div class="col-12 col-lg-8">

            <div class="table-card-custom">

                <!-- Table Header -->
                <div class="table-header-control">

                    <div class="table-search-box">

                        <i class="bi bi-search table-search-icon"></i>

                        <input
                            type="text"
                            class="table-search-input"
                            id="tagSearch"
                            placeholder="Search tags..."
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
                                <th>Name</th>
                                <th>Research Count</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>

                                    <div class="table-user-cell">

                                        <div class="table-user-avatar">
                                            <i class="bi bi-tag"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Machine Learning
                                            </div>

                                            <div class="table-user-sub">
                                                machine-learning
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    18 Research
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/research/tags.php?action=update') ?>"
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
                                            <i class="bi bi-tag"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Deep Learning
                                            </div>

                                            <div class="table-user-sub">
                                                deep-learning
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    14 Research
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/research/tags.php?action=update') ?>"
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
                                            <i class="bi bi-tag"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Natural Language Processing
                                            </div>

                                            <div class="table-user-sub">
                                                natural-language-processing
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    11 Research
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/research/tags.php?action=update') ?>"
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
                                            <i class="bi bi-tag"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Computer Vision
                                            </div>

                                            <div class="table-user-sub">
                                                computer-vision
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    9 Research
                                </td>

                                <td>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/research/tags.php?action=update') ?>"
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
                                            <i class="bi bi-tag"></i>
                                        </div>

                                        <div>

                                            <div class="table-user-name">
                                                Cyber Security
                                            </div>

                                            <div class="table-user-sub">
                                                cyber-security
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    15 Research
                                </td>

                                <td>

                                    <span class="badge-table failed">
                                        Inactive
                                    </span>

                                </td>

                                <td>

                                    <div class="d-flex justify-content-center gap-1">

                                        <a
                                            href="<?= url('admin/research/tags.php?action=update') ?>"
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
                        Showing 1 to 5 of 28 records
                    </span>

                    <nav aria-label="Research tag pagination">

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

        <!-- Add Research Tag -->
        <div class="col-12 col-lg-4">

            <div class="card border-light shadow-sm p-4">

                <h5 class="card-title mb-4">
                    <?= $buttonText ?> Tag
                </h5>

                <form action="" method="post">

                    <!-- Name -->
                    <div class="mb-3">

                        <label for="itemName" class="form-label-custom">
                            Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control-custom"
                            id="itemName"
                            name="name"
                            placeholder="Enter tag name"
                            value="<?= $tagForm['name'] ?>"
                            required
                        >

                    </div>

                    <!-- Description -->
                    <div class="mb-3">

                        <label for="itemDescription" class="form-label-custom">
                            Description
                        </label>

                        <textarea
                            class="form-control-custom"
                            id="itemDescription"
                            name="description"
                            rows="5"
                            placeholder="Enter a short description..."
                        ><?= $tagForm['description'] ?></textarea>

                    </div>

                    <!-- Status -->
                    <div class="mb-4">

                        <label for="itemStatus" class="form-label-custom">
                            Status
                        </label>

                        <select
                            class="form-select-custom"
                            id="itemStatus"
                            name="status"
                        >

                            <option
                                value="1"
                                <?= $tagForm['status'] == 1 ? 'selected' : '' ?>
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                <?= $tagForm['status'] == 0 ? 'selected' : '' ?>
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

                            <?= $buttonText ?> Tag

                        </button>

                        <?php if ($buttonText == 'Update'): ?>

                            <a
                                href="<?= url('admin/research/tags.php?action=create') ?>"
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