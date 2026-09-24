<?php
require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$isCreate = page_action() === 'create';

$pageType = $isCreate ? "Add Student" : "Edit Student";
$buttonText = $isCreate ? "Add" : "Update";

$pageHeader = $pageType;
$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Student Management" => url('admin/university/students/index.php'),
    $pageType => ""
];

$studentForm = [
    "student_id" => $isCreate ? "" : "3917",
    "name" => $isCreate ? "" : "Sobuj",
    "email" => $isCreate ? "" : "",
    "phone" => $isCreate ? "" : "",
    "department_id" => $isCreate ? "" : "1",
    "batch" => $isCreate ? "" : "65B",
    "academic_session_id" => $isCreate ? "" : "1",
    "gender" => $isCreate ? "" : "Male",
    "date_of_birth" => $isCreate ? "" : "",
    "completed_credit_hours" => $isCreate ? "" : "122",
    "cgpa" => $isCreate ? "" : "3.70",
    "current_semester" => $isCreate ? "" : "",
    "enrollment_date" => $isCreate ? "" : "",
    "photo" => $isCreate ? "" : "",
    "address" => $isCreate ? "" : "",
    "profile" => $isCreate ? "" : "",
    "status" => $isCreate ? 1 : 1,
];
?>

<div class="main-wrapper">

    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <form action="" method="post" enctype="multipart/form-data">

        <!-- Student Information -->
        <div class="card border-light shadow-sm p-4 mb-4">

            <div class="mb-3">

                <h5 class="mb-1">
                    <?= $pageType ?>
                </h5>

                <p class="text-muted mb-0">
                    Enter student information and academic details.
                </p>

            </div>

            <?php if (!$isCreate && !empty($studentForm['photo'])): ?>

            <!-- Current Student Photo -->
            <div class="text-center mb-4">

                <img src="<?= htmlspecialchars($studentForm['photo']) ?>"
                    alt="<?= htmlspecialchars($studentForm['name']) ?>" width="200" height="200"
                    style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px;">

                <div class="mt-2 text-muted small">
                    Current Student Photo
                </div>

            </div>

            <?php endif; ?>

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label for="student_id" class="form-label-custom">
                        Student ID <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="student_id" id="student_id" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['student_id']) ?>" placeholder="Enter student ID"
                        required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="name" class="form-label-custom">
                        Student Name <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="name" id="name" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['name']) ?>" placeholder="Enter student name" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="email" class="form-label-custom">
                        Email <span class="text-danger">*</span>
                    </label>

                    <input type="email" name="email" id="email" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['email']) ?>" placeholder="Enter email address"
                        required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="phone" class="form-label-custom">
                        Phone
                    </label>

                    <input type="text" name="phone" id="phone" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['phone']) ?>" placeholder="Enter phone number">
                </div>

                <div class="col-12 col-md-6">
                    <label for="department_id" class="form-label-custom">
                        Department <span class="text-danger">*</span>
                    </label>

                    <select name="department_id" id="department_id" class="form-select-custom" required>
                        <option value="">Select Department</option>

                        <option value="1" <?= $studentForm['department_id'] == '1' ? 'selected' : '' ?>>
                            Computer Science & Engineering
                        </option>

                        <option value="2" <?= $studentForm['department_id'] == '2' ? 'selected' : '' ?>>
                            Electrical & Electronic Engineering
                        </option>

                        <option value="3" <?= $studentForm['department_id'] == '3' ? 'selected' : '' ?>>
                            Information Technology
                        </option>

                        <option value="4" <?= $studentForm['department_id'] == '4' ? 'selected' : '' ?>>
                            Business Administration
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="batch" class="form-label-custom">
                        Batch <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="batch" id="batch" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['batch']) ?>" placeholder="e.g. 65B" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="academic_session_id" class="form-label-custom">
                        Academic Session <span class="text-danger">*</span>
                    </label>

                    <select name="academic_session_id" id="academic_session_id" class="form-select-custom" required>
                        <option value="">Select Academic Session</option>

                        <option value="1" <?= $studentForm['academic_session_id'] == '1' ? 'selected' : '' ?>>
                            2025 - 2026
                        </option>

                        <option value="2" <?= $studentForm['academic_session_id'] == '2' ? 'selected' : '' ?>>
                            2024 - 2025
                        </option>

                        <option value="3" <?= $studentForm['academic_session_id'] == '3' ? 'selected' : '' ?>>
                            2023 - 2024
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="gender" class="form-label-custom">
                        Gender
                    </label>

                    <select name="gender" id="gender" class="form-select-custom">
                        <option value="">Select Gender</option>

                        <option value="Male" <?= $studentForm['gender'] === 'Male' ? 'selected' : '' ?>>
                            Male
                        </option>

                        <option value="Female" <?= $studentForm['gender'] === 'Female' ? 'selected' : '' ?>>
                            Female
                        </option>

                        <option value="Other" <?= $studentForm['gender'] === 'Other' ? 'selected' : '' ?>>
                            Other
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="date_of_birth" class="form-label-custom">
                        Date of Birth
                    </label>

                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['date_of_birth']) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label for="completed_credit_hours" class="form-label-custom">
                        Completed Credit Hours <span class="text-danger">*</span>
                    </label>

                    <input type="number" name="completed_credit_hours" id="completed_credit_hours"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['completed_credit_hours']) ?>" placeholder="e.g. 122"
                        min="0" step="1" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="cgpa" class="form-label-custom">
                        CGPA <span class="text-danger">*</span>
                    </label>

                    <input type="number" name="cgpa" id="cgpa" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['cgpa']) ?>" placeholder="e.g. 3.70" min="0" max="4"
                        step="0.01" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="current_semester" class="form-label-custom">
                        Current Semester
                    </label>

                    <select name="current_semester" id="current_semester" class="form-select-custom">
                        <option value="">Select Semester</option>

                        <option value="1st Semester"
                            <?= $studentForm['current_semester'] === '1st Semester' ? 'selected' : '' ?>>
                            1st Semester
                        </option>

                        <option value="2nd Semester"
                            <?= $studentForm['current_semester'] === '2nd Semester' ? 'selected' : '' ?>>
                            2nd Semester
                        </option>

                        <option value="3rd Semester"
                            <?= $studentForm['current_semester'] === '3rd Semester' ? 'selected' : '' ?>>
                            3rd Semester
                        </option>

                        <option value="4th Semester"
                            <?= $studentForm['current_semester'] === '4th Semester' ? 'selected' : '' ?>>
                            4th Semester
                        </option>

                        <option value="5th Semester"
                            <?= $studentForm['current_semester'] === '5th Semester' ? 'selected' : '' ?>>
                            5th Semester
                        </option>

                        <option value="6th Semester"
                            <?= $studentForm['current_semester'] === '6th Semester' ? 'selected' : '' ?>>
                            6th Semester
                        </option>

                        <option value="7th Semester"
                            <?= $studentForm['current_semester'] === '7th Semester' ? 'selected' : '' ?>>
                            7th Semester
                        </option>

                        <option value="8th Semester"
                            <?= $studentForm['current_semester'] === '8th Semester' ? 'selected' : '' ?>>
                            8th Semester
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="enrollment_date" class="form-label-custom">
                        Enrollment Date
                    </label>

                    <input type="date" name="enrollment_date" id="enrollment_date" class="form-control-custom"
                        value="<?= htmlspecialchars($studentForm['enrollment_date']) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label for="photo" class="form-label-custom">
                        <?= $isCreate ? 'Student Photo' : 'Change Student Photo' ?>
                    </label>

                    <input type="file" name="photo" id="photo" class="form-control-custom"
                        accept="image/jpeg,image/png,image/webp">

                    <small class="text-muted">
                        JPG, PNG or WebP
                    </small>
                </div>

                <div class="col-12 col-md-6">
                    <label for="status" class="form-label-custom">
                        Status
                    </label>

                    <select name="status" id="status" class="form-select-custom">
                        <option value="1" <?= $studentForm['status'] == 1 ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="0" <?= $studentForm['status'] == 0 ? 'selected' : '' ?>>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="col-12">
                    <label for="address" class="form-label-custom">
                        Address
                    </label>

                    <textarea name="address" id="address" rows="2" class="form-control-custom"
                        placeholder="Enter student address"><?= htmlspecialchars($studentForm['address']) ?></textarea>
                </div>

                <div class="col-12">
                    <label for="profile" class="form-label-custom">
                        Profile / Additional Information
                    </label>

                    <textarea name="profile" id="profile" rows="3" class="form-control-custom"
                        placeholder="Enter student profile or additional information"><?= htmlspecialchars($studentForm['profile']) ?></textarea>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-2">

                    <a href="<?= url('admin/university/students/index.php') ?>" class="btn btn-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <?= $buttonText ?> Student
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>