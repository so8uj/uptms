<?php
require_once(__DIR__ . '/../../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Research Library"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Research Library" => ""];
$research = [["title" => "AI-Based Student Performance Prediction System", "author" => "Mohammad Sobuj", "category" => "Artificial Intelligence", "department" => "Computer Science", "year" => "2026", "type" => "Thesis"], ["title" => "Smart Campus Management Using IoT", "author" => "Nusrat Jahan", "category" => "Internet of Things", "department" => "Computer Science", "year" => "2025", "type" => "Research Paper"], ["title" => "Secure Data Sharing for Academic Networks", "author" => "Tanvir Hasan", "category" => "Cyber Security", "department" => "Information Technology", "year" => "2026", "type" => "Project"]];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="table-search-box">
<i class="bi bi-search table-search-icon">
</i>
<input
                            class="table-search-input" placeholder="Search title, author, keyword...">
</div>
                </div>
                <div class="col-12 col-md-3">
<select class="form-select-custom">
                        <option>All Categories</option>
                        <option>Artificial Intelligence</option>
                        <option>Internet of Things</option>
                        <option>Cyber Security</option>
                    </select>
</div>
                <div class="col-12 col-md-3">
<a href="<?= url('teacher/research/manage.php?action=create') ?>"
                        class="btn btn-primary w-100">
<i class="bi bi-plus-lg me-1">
</i>Add Research</a>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Research</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Type</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($research as $item): ?>
<tr>
                        <td class="table-user-name">
<?= htmlspecialchars($item['title']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['author']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['category']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['department']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['year']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['type']) ?>
</td>
                        <td class="text-center">
<a href="#" class="table-btn-action" title="View research">
<i
                                    class="bi bi-eye">
</i>
</a>
</td>
                    </tr>
<?php endforeach; ?>
</tbody>
</table>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>