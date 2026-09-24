<?php
require_once(__DIR__ . '/../../app/bootstrap.php');


require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$isCreate = page_action() === 'create';
$pageType = $isCreate ? "Add Project" : "Edit Project";
$buttonText = $isCreate ? "Add Project" : "Update Project";

$pageHeader = $pageType;
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Project Management" => url('admin/projects/index.php'),
    $pageType => ""
];

$project = [
    "title" => $isCreate ? "" : "University Thesis Management System",
    "project_code" => $isCreate ? "" : "PRJ-2026-001",
    "department" => $isCreate ? "" : "cse",
    "academic_session" => $isCreate ? "" : "2025-2026",
    "team" => $isCreate ? "" : "Team Alpha",
    "supervisor" => $isCreate ? "" : "1",
    "category" => $isCreate ? "" : "software",
    "status" => $isCreate ? "pending" : "ongoing",
    "description" => $isCreate ? "" : "A university platform for managing thesis projects, teams, milestones, documents, and evaluations."
];
?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <form action="" method="post">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Project Information</h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label for="title" class="form-label-custom">Project Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control-custom"
                                value="<?= htmlspecialchars($project['title']) ?>" placeholder="Enter project title"
                                required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="project_code" class="form-label-custom">Project Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="project_code" name="project_code" class="form-control-custom"
                                value="<?= htmlspecialchars($project['project_code']) ?>"
                                placeholder="e.g. PRJ-2026-001" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="department" class="form-label-custom">Department <span
                                    class="text-danger">*</span></label>
                            <select id="department" name="department" class="form-select-custom" required>
                                <option value="">Select Department</option>
                                <option value="cse" <?= $project['department'] === 'cse' ? 'selected' : '' ?>>Computer
                                    Science & Engineering</option>
                                <option value="eee" <?= $project['department'] === 'eee' ? 'selected' : '' ?>>Electrical
                                    & Electronic Engineering</option>
                                <option value="it" <?= $project['department'] === 'it' ? 'selected' : '' ?>>Information
                                    Technology</option>
                                <option value="bba" <?= $project['department'] === 'bba' ? 'selected' : '' ?>>Business
                                    Administration</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="academic_session" class="form-label-custom">Academic Session <span
                                    class="text-danger">*</span></label>
                            <select id="academic_session" name="academic_session" class="form-select-custom" required>
                                <option value="">Select Academic Session</option>
                                <option value="2025-2026"
                                    <?= $project['academic_session'] === '2025-2026' ? 'selected' : '' ?>>2025 - 2026
                                </option>
                                <option value="2024-2025"
                                    <?= $project['academic_session'] === '2024-2025' ? 'selected' : '' ?>>2024 - 2025
                                </option>
                                <option value="2023-2024"
                                    <?= $project['academic_session'] === '2023-2024' ? 'selected' : '' ?>>2023 - 2024
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="team" class="form-label-custom">Project Team <span
                                    class="text-danger">*</span></label>
                            <select id="team" name="team" class="form-select-custom" required>
                                <option value="">Select Project Team</option>
                                <option value="Team Alpha" <?= $project['team'] === 'Team Alpha' ? 'selected' : '' ?>>
                                    Team Alpha</option>
                                <option value="Team Nova" <?= $project['team'] === 'Team Nova' ? 'selected' : '' ?>>Team
                                    Nova</option>
                                <option value="Team Vertex" <?= $project['team'] === 'Team Vertex' ? 'selected' : '' ?>>
                                    Team Vertex</option>
                                <option value="Team Insight"
                                    <?= $project['team'] === 'Team Insight' ? 'selected' : '' ?>>Team Insight</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="supervisor" class="form-label-custom">Supervisor</label>
                            <select id="supervisor" name="supervisor" class="form-select-custom">
                                <option value="">Select Supervisor</option>
                                <option value="1" <?= $project['supervisor'] === '1' ? 'selected' : '' ?>>Dr. Mahmud
                                    Hasan</option>
                                <option value="2" <?= $project['supervisor'] === '2' ? 'selected' : '' ?>>Dr. Farhana
                                    Rahman</option>
                                <option value="3" <?= $project['supervisor'] === '3' ? 'selected' : '' ?>>Prof. Rezaul
                                    Karim</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="category" class="form-label-custom">Project Category <span
                                    class="text-danger">*</span></label>
                            <select id="category" name="category" class="form-select-custom" required>
                                <option value="">Select Category</option>
                                <option value="software" <?= $project['category'] === 'software' ? 'selected' : '' ?>>
                                    Software Engineering</option>
                                <option value="ai" <?= $project['category'] === 'ai' ? 'selected' : '' ?>>Artificial
                                    Intelligence</option>
                                <option value="iot" <?= $project['category'] === 'iot' ? 'selected' : '' ?>>Internet of
                                    Things</option>
                                <option value="cybersecurity"
                                    <?= $project['category'] === 'cybersecurity' ? 'selected' : '' ?>>Cyber Security
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="status" class="form-label-custom">Project Status <span
                                    class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select-custom" required>
                                <option value="pending" <?= $project['status'] === 'pending' ? 'selected' : '' ?>>
                                    Pending</option>
                                <option value="ongoing" <?= $project['status'] === 'ongoing' ? 'selected' : '' ?>>
                                    Ongoing</option>
                                <option value="completed" <?= $project['status'] === 'completed' ? 'selected' : '' ?>>
                                    Completed</option>
                                <option value="archived" <?= $project['status'] === 'archived' ? 'selected' : '' ?>>
                                    Archived</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label-custom">Project Description <span
                                    class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control-custom" rows="6"
                                placeholder="Enter project description..."
                                required><?= htmlspecialchars($project['description']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('admin/projects/index.php') ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i
                            class="bi bi-check-lg me-1"></i><?= $buttonText ?></button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>