<?php
require_once("../../includes/header.php");
require_once("../../includes/sidebar.php");

$isCreate = page_action() === 'create';

$pageType = $isCreate ? "Add Teacher" : "Edit Teacher";
$buttonText = $isCreate ? "Add" : "Update";

$pageHeader = $pageType;
$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Teacher Management" => url('admin/university/teachers/index.php'),
    $pageType => ""
];

$teacherForm = [
    "name" => $isCreate ? "" : "Dr. Jannatul Naeem",
    "employee_id" => $isCreate ? "" : "T-1001",
    "email" => $isCreate ? "" : "",
    "phone" => $isCreate ? "" : "",
    "department_id" => $isCreate ? "" : "1",
    "designation" => $isCreate ? "" : "Assistant Professor",
    "specialization" => $isCreate ? "" : "",
    "research_area" => $isCreate ? "" : "Head of the Department",
    "profile" => $isCreate ? "" : "",
    "photo" => $isCreate
        ? ""
        : "https://wub.edu.bd/admin-assets/images/employeeImg_thumb/Dr-Jannatul-Naeem.jpg",
    "profile_url" => $isCreate
        ? ""
        : "https://cse.wub.edu.bd/main/faculty_member_details/918",
    "status" => $isCreate ? 1 : 1,
];
?>

<div class="main-wrapper">

    <?php
    require_once("../../includes/dashboard_header.php");
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <form action="" method="post" enctype="multipart/form-data">

        <!-- Teacher Information -->
        <div class="card border-light shadow-sm p-4 mb-4">

            <div class="mb-3">

                <h5 class="mb-1">
                    <?= $pageType ?>
                </h5>

                <p class="text-muted mb-0">
                    Enter teacher information and academic details.
                </p>

            </div>

            <?php if (!$isCreate && !empty($teacherForm['photo'])): ?>

                <!-- Current Teacher Photo -->
                <div class="text-center mb-4">

                    <img
                        src="<?= htmlspecialchars($teacherForm['photo']) ?>"
                        alt="<?= htmlspecialchars($teacherForm['name']) ?>"
                        width="200"
                        height="200"
                        style="width: 200px; height: 200px; object-fit: cover; border-radius: 10px;"
                    >

                    <div class="mt-2 text-muted small">
                        Current Teacher Photo
                    </div>

                </div>

            <?php endif; ?>

            <div class="row g-3">

                <!-- Teacher Name -->
                <div class="col-12 col-md-6">

                    <label for="name" class="form-label-custom">
                        Teacher Name
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['name']) ?>"
                        placeholder="Enter teacher name"
                        required
                    >

                </div>

                <!-- Employee ID -->
                <div class="col-12 col-md-6">

                    <label for="employee_id" class="form-label-custom">
                        Employee ID
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="employee_id"
                        id="employee_id"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['employee_id']) ?>"
                        placeholder="Enter employee ID"
                        required
                    >

                </div>

                <!-- Email -->
                <div class="col-12 col-md-6">

                    <label for="email" class="form-label-custom">
                        Email
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['email']) ?>"
                        placeholder="Enter email address"
                        required
                    >

                </div>

                <!-- Phone -->
                <div class="col-12 col-md-6">

                    <label for="phone" class="form-label-custom">
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="phone"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['phone']) ?>"
                        placeholder="Enter phone number"
                    >

                </div>

                <!-- Department -->
                <div class="col-12 col-md-6">

                    <label for="department_id" class="form-label-custom">
                        Department
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="department_id"
                        id="department_id"
                        class="form-select-custom"
                        required
                    >

                        <option value="">
                            Select Department
                        </option>

                        <option
                            value="1"
                            <?= $teacherForm['department_id'] == '1' ? 'selected' : '' ?>
                        >
                            Computer Science & Engineering
                        </option>

                        <option
                            value="2"
                            <?= $teacherForm['department_id'] == '2' ? 'selected' : '' ?>
                        >
                            Electrical & Electronic Engineering
                        </option>

                        <option
                            value="3"
                            <?= $teacherForm['department_id'] == '3' ? 'selected' : '' ?>
                        >
                            Information Technology
                        </option>

                        <option
                            value="4"
                            <?= $teacherForm['department_id'] == '4' ? 'selected' : '' ?>
                        >
                            Business Administration
                        </option>

                    </select>

                </div>

                <!-- Designation -->
                <div class="col-12 col-md-6">

                    <label for="designation" class="form-label-custom">
                        Designation
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="designation"
                        id="designation"
                        class="form-select-custom"
                        required
                    >

                        <option value="">
                            Select Designation
                        </option>

                        <option
                            value="Professor"
                            <?= $teacherForm['designation'] === 'Professor' ? 'selected' : '' ?>
                        >
                            Professor
                        </option>

                        <option
                            value="Associate Professor"
                            <?= $teacherForm['designation'] === 'Associate Professor' ? 'selected' : '' ?>
                        >
                            Associate Professor
                        </option>

                        <option
                            value="Assistant Professor"
                            <?= $teacherForm['designation'] === 'Assistant Professor' ? 'selected' : '' ?>
                        >
                            Assistant Professor
                        </option>

                        <option
                            value="Senior Lecturer"
                            <?= $teacherForm['designation'] === 'Senior Lecturer' ? 'selected' : '' ?>
                        >
                            Senior Lecturer
                        </option>

                        <option
                            value="Lecturer"
                            <?= $teacherForm['designation'] === 'Lecturer' ? 'selected' : '' ?>
                        >
                            Lecturer
                        </option>

                    </select>

                </div>

                <!-- Specialization -->
                <div class="col-12 col-md-6">

                    <label for="specialization" class="form-label-custom">
                        Specialization / Research Area
                    </label>

                    <input
                        type="text"
                        name="specialization"
                        id="specialization"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['specialization']) ?>"
                        placeholder="e.g. Artificial Intelligence"
                    >

                </div>

                <!-- Research Area / Role -->
                <div class="col-12 col-md-6">

                    <label for="research_area" class="form-label-custom">
                        Research Area / Role
                    </label>

                    <input
                        type="text"
                        name="research_area"
                        id="research_area"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['research_area']) ?>"
                        placeholder="Enter research area or role"
                    >

                </div>

                <!-- Teacher Photo -->
                <div class="col-12 col-md-6">

                    <label for="photo" class="form-label-custom">
                        <?= $isCreate ? 'Teacher Photo' : 'Change Teacher Photo' ?>
                    </label>

                    <input
                        type="file"
                        name="photo"
                        id="photo"
                        class="form-control-custom"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small class="text-muted">
                        JPG, PNG or WebP
                    </small>

                </div>

                <!-- Profile URL -->
                <div class="col-12 col-md-6">

                    <label for="profile_url" class="form-label-custom">
                        Faculty Profile URL
                    </label>

                    <input
                        type="url"
                        name="profile_url"
                        id="profile_url"
                        class="form-control-custom"
                        value="<?= htmlspecialchars($teacherForm['profile_url']) ?>"
                        placeholder="https://..."
                    >

                </div>

                <!-- Status -->
                <div class="col-12 col-md-6">

                    <label for="status" class="form-label-custom">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-select-custom"
                    >

                        <option
                            value="1"
                            <?= $teacherForm['status'] == 1 ? 'selected' : '' ?>
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            <?= $teacherForm['status'] == 0 ? 'selected' : '' ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>

                <!-- Profile -->
                <div class="col-12">

                    <label for="profile" class="form-label-custom">
                        Profile / Additional Information
                    </label>

                    <textarea
                        name="profile"
                        id="profile"
                        rows="3"
                        class="form-control-custom"
                        placeholder="Enter teacher profile or additional information"
                    ><?= htmlspecialchars($teacherForm['profile']) ?></textarea>

                </div>

                <!-- Form Actions -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">

                    <a
                        href="<?= url('admin/university/teachers/index.php') ?>"
                        class="btn btn-light"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <?= $buttonText ?> Teacher
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

<?php require_once("../../includes/footer.php"); ?>