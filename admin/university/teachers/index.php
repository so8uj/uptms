<?php
require_once("../../includes/header.php");
require_once("../../includes/sidebar.php");

$pageHeader = "Teacher Management";
$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Teacher Management" => ""
];
?>

<div class="main-wrapper">

    <?php
    require_once("../../includes/dashboard_header.php");
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <!-- Teacher Management Content -->
    <div class="row">
        <div class="col-12">

            <div class="card border-light shadow-sm">

                <div class="card-body">

                    <!-- Table Header -->
                    <div
                        class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">

                        <div>
                            <h5 class="mb-1">Teachers</h5>
                            <p class="text-muted mb-0">
                                Manage university teachers and supervisors.
                            </p>
                        </div>

                        <div>
                            <a href="<?= url('admin/university/teachers/manage.php?action=create') ?>"
                                class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                Add Teacher
                            </a>
                        </div>

                    </div>

                    <!-- Search and Filter -->
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">

                        <div class="table-search-box">

                            <div class="input-group input-group-custom">

                                <span class="input-group-text input-group-text-custom">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input type="text" class="form-control form-control-custom table-search-input"
                                    placeholder="Search teacher...">

                            </div>

                        </div>

                        <div class="table-filter-group d-flex align-items-center gap-2">

                            <div class="dropdown">

                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    Status
                                </button>

                                <ul class="dropdown-menu">

                                    <li>
                                        <a class="dropdown-item" href="#">
                                            All
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

                            <button type="button" class="btn btn-light">
                                <i class="bi bi-download me-1"></i>
                                Export
                            </button>

                        </div>

                    </div>

                    <!-- Teacher Table -->
                    <div class="table-responsive">

                        <table class="table table-custom align-middle mb-0">

                            <thead>

                                <tr>
                                    <th>Teacher</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Research Area / Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <!-- Dr. Jannatul Naeem -->
                                <tr>

                                    <td>
                                        <div class="table-user-cell">

                                            <div class="table-user-avatar">
                                                <img class="table-user-avatar" src="https://wub.edu.bd/admin-assets/images/employeeImg_thumb/Dr-Jannatul-Naeem.jpg"
                                                    alt="Dr. Jannatul Naeem">
                                            </div>

                                            <div>
                                                <div class="table-user-name">
                                                    Dr. Jannatul Naeem
                                                </div>

                                                <div class="table-user-sub">
                                                    Head of the Department
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        T-1001
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        Assistant Professor
                                    </td>

                                    <td>
                                        Head of the Department
                                    </td>

                                    <td>
                                        <span class="badge-table success">
                                            Active
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end gap-1">

                                            <a href="https://cse.wub.edu.bd/main/faculty_member_details/918"
                                                target="_blank" class="table-btn-action" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="<?= url('admin/university/teachers/manage.php?action=update&id=1') ?>"
                                                class="table-btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="#" class="table-btn-action delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>
                                    </td>

                                </tr>

                                <!-- Kazi H. Robin -->
                                <tr>

                                    <td>
                                        <div class="table-user-cell">

                                            <div class="table-user-avatar">
                                                <img class="table-user-avatar" src="https://wub.edu.bd/admin-assets/images/employeeImg_thumb/1748539101_01820547352.jpg"
                                                    alt="Kazi H. Robin">
                                            </div>

                                            <div>
                                                <div class="table-user-name">
                                                    Kazi H. Robin
                                                </div>

                                                <div class="table-user-sub">
                                                    Associate Professor
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        T-1002
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        Associate Professor
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        <span class="badge-table success">
                                            Active
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end gap-1">

                                            <a href="https://cse.wub.edu.bd/main/faculty_member_details/79"
                                                target="_blank" class="table-btn-action" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="<?= url('admin/university/teachers/manage.php?action=update&id=2') ?>"
                                                class="table-btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="#" class="table-btn-action delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>
                                    </td>

                                </tr>

                                <!-- Ahsan Ullah -->
                                <tr>

                                    <td>
                                        <div class="table-user-cell">

                                            <div class="table-user-avatar">
                                                <img class="table-user-avatar" src="https://wub.edu.bd/admin-assets/images/employeeImg_thumb/Ahsan-Ullah1.jpg"
                                                    alt="Ahsan Ullah">
                                            </div>

                                            <div>
                                                <div class="table-user-name">
                                                    Ahsan Ullah
                                                </div>

                                                <div class="table-user-sub">
                                                    Assistant Professor
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        T-1003
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        Assistant Professor
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        <span class="badge-table success">
                                            Active
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end gap-1">

                                            <a href="https://cse.wub.edu.bd/main/faculty_member_details/354"
                                                target="_blank" class="table-btn-action" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="<?= url('admin/university/teachers/manage.php?action=update&id=3') ?>"
                                                class="table-btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="#" class="table-btn-action delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>
                                    </td>

                                </tr>

                                <!-- Shamsun Nahar -->
                                <tr>

                                    <td>
                                        <div class="table-user-cell">

                                            <div class="table-user-avatar">
                                                <img class="table-user-avatar" src="https://wub.edu.bd/admin-assets/images/employeeImg_thumb/Shamsun-Nahar.jpg"
                                                    alt="Shamsun Nahar">
                                            </div>

                                            <div>
                                                <div class="table-user-name">
                                                    Shamsun Nahar
                                                </div>

                                                <div class="table-user-sub">
                                                    Assistant Professor
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        T-1004
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        Assistant Professor
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        <span class="badge-table success">
                                            Active
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end gap-1">

                                            <a href="https://cse.wub.edu.bd/main/faculty_member_details/462"
                                                target="_blank" class="table-btn-action" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="<?= url('admin/university/teachers/manage.php?action=update&id=4') ?>"
                                                class="table-btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="#" class="table-btn-action delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>
                                    </td>

                                </tr>

                                <!-- Dr. Md Amran Hossen -->
                                <tr>

                                    <td>
                                        <div class="table-user-cell">

                                            <div class="table-user-avatar">
                                                <img class="table-user-avatar" src="https://wub.edu.bd/admin-assets/images/employeeImg_thumb/4.jpg"
                                                    alt="Dr. Md Amran Hossen">
                                            </div>

                                            <div>
                                                <div class="table-user-name">
                                                    Dr. Md Amran Hossen
                                                </div>

                                                <div class="table-user-sub">
                                                    Assistant Professor
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        T-1005
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        Assistant Professor
                                    </td>

                                    <td>
                                        Computer Science & Engineering
                                    </td>

                                    <td>
                                        <span class="badge-table success">
                                            Active
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end gap-1">

                                            <a href="https://cse.wub.edu.bd/main/faculty_member_details/957"
                                                target="_blank" class="table-btn-action" title="View Profile">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="<?= url('admin/university/teachers/manage.php?action=update&id=5') ?>"
                                                class="table-btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="#" class="table-btn-action delete" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>

                                        </div>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <!-- Table Footer -->
                    <div class="table-footer-control mt-4">

                        <div class="table-pagination-info">
                            Showing 1 to 4 of 4 teachers
                        </div>

                        <nav>

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

                                <li class="page-item disabled">
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

</div>

<?php require_once("../../includes/footer.php"); ?>