<?php
require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Student Management";
$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Student Management" => ""
];

$students = [
    [
        "id" => 3917,
        "name" => "Sobuj",
        "photo" => "",
        "email" => "",
        "department" => "Computer Science & Engineering",
        "batch" => "65B",
        "credit_hours" => 122,
        "cgpa" => "3.70",
        "status" => 1,
    ],
    [
        "id" => 3919,
        "name" => "Md Adil Miah",
        "photo" => "",
        "email" => "",
        "department" => "Computer Science & Engineering",
        "batch" => "65B",
        "credit_hours" => 122,
        "cgpa" => "3.60",
        "status" => 1,
    ],
    [
        "id" => 3921,
        "name" => "Prodip Rajbongshi",
        "photo" => "",
        "email" => "",
        "department" => "Computer Science & Engineering",
        "batch" => "65B",
        "credit_hours" => 122,
        "cgpa" => "3.55",
        "status" => 1,
    ],
];
?>

<div class="main-wrapper">

    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <!-- Student Management Content -->
    <div class="card border-light shadow-sm">

        <div class="card-body border-bottom">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div>
                    <h5 class="mb-1">Students</h5>
                    <p class="text-muted mb-0">
                        Manage university students and their academic information.
                    </p>
                </div>

                <a
                    href="<?= url('admin/university/students/manage.php?action=create') ?>"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Student
                </a>

            </div>

        </div>

        <div class="card-body border-bottom">

            <div class="row g-3">

                <div class="col-12 col-md-5">
                    <div class="table-search-box">
                        <input
                            type="text"
                            class="table-search-input"
                            placeholder="Search student by ID or name..."
                        >
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Departments</option>
                        <option value="cse">Computer Science & Engineering</option>
                        <option value="eee">Electrical & Electronic Engineering</option>
                        <option value="it">Information Technology</option>
                        <option value="bba">Business Administration</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Batches</option>
                        <option value="65B">65B</option>
                        <option value="65A">65A</option>
                        <option value="64B">64B</option>
                        <option value="64A">64A</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-custom align-middle mb-0">

                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Batch</th>
                        <th>Completed Credit Hours</th>
                        <th>CGPA</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($students as $student): ?>

                        <tr>

                            <td>
                                <div class="table-user-cell">

                                    <div class="table-user-avatar">

                                        <?php if (!empty($student['photo'])): ?>

                                            <img
                                                src="<?= htmlspecialchars($student['photo']) ?>"
                                                alt="<?= htmlspecialchars($student['name']) ?>"
                                                style="width: 100%; height: 100%; object-fit: cover;"
                                            >

                                        <?php else: ?>

                                            <?= strtoupper(substr($student['name'], 0, 1)) ?>

                                        <?php endif; ?>

                                    </div>

                                    <div>
                                        <div class="table-user-name">
                                            <?= htmlspecialchars($student['name']) ?>
                                        </div>

                                        <div class="table-user-sub">
                                            <?= !empty($student['email'])
                                                ? htmlspecialchars($student['email'])
                                                : 'Student'
                                            ?>
                                        </div>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="fw-medium">
                                    <?= htmlspecialchars($student['id']) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['batch']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['credit_hours']) ?> Credits
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    <?= htmlspecialchars($student['cgpa']) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($student['department']) ?>
                            </td>

                            <td>

                                <?php if ($student['status']): ?>

                                    <span class="badge-table success">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="badge-table failed">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="d-flex justify-content-end gap-1">

                                    <a
                                        href="<?= url('admin/university/students/manage.php?action=update&id=' . $student['id']) ?>"
                                        class="table-btn-action"
                                        title="View Student"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a
                                        href="<?= url('admin/university/students/manage.php?action=update&id=' . $student['id']) ?>"
                                        class="table-btn-action"
                                        title="Edit Student"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a
                                        href="#"
                                        class="table-btn-action delete"
                                        title="Delete Student"
                                        onclick="return confirm('Are you sure you want to delete this student?');"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <div class="card-body border-top">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div class="table-pagination-info">
                    Showing 1 to <?= count($students) ?> of <?= count($students) ?> students
                </div>

                <div class="table-footer-control">

                    <nav>
                        <ul class="pagination mb-0">

                            <li class="page-item disabled">
                                <a class="page-link" href="#">Previous</a>
                            </li>

                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
                            </li>

                            <li class="page-item disabled">
                                <a class="page-link" href="#">Next</a>
                            </li>

                        </ul>
                    </nav>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
