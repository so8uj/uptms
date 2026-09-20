<?php 
require_once("../includes/header.php"); 
require_once("../includes/sidebar.php"); 

$pageHeader = "Research Library";
$showBreadcrumb = true;
$breadcrumbs = [
   "Dashboard" => url('admin/index.php'),
   "Research Library" => "",
];


?>

<div class="main-wrapper">
    <?php  
      require_once("../includes/dashboard_header.php"); 
      require_once(base_path('includes/admin/page_header_breadcrumb.php')); 
   ?>

    <!-- Reseach Library Content -->

    <div class="table-card-custom">

        <!-- Header Controls -->
        <div class="table-header-control">

            <!-- Search -->
            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>

                <input type="text" class="table-search-input" id="researchSearch"
                    placeholder="Search research title, author, keyword...">
            </div>

            <!-- Filters / Actions -->
            <div class="table-filter-group">

                <!-- Category -->
                <div class="dropdown">
                    <button class="btn-table-action dropdown-toggle" type="button" id="dropdownResearchCategory"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-folder2-open"></i>
                        Category
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownResearchCategory">
                        <li>
                            <a class="dropdown-item" href="#">All Categories</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Computer Science</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Artificial Intelligence</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Software Engineering</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Cyber Security</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Data Science</a>
                        </li>
                    </ul>
                </div>

                <!-- Year -->
                <div class="dropdown">
                    <button class="btn-table-action dropdown-toggle" type="button" id="dropdownResearchYear"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-calendar3"></i>
                        Year
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownResearchYear">
                        <li>
                            <a class="dropdown-item" href="#">All Years</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">2026</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">2025</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">2024</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">2023</a>
                        </li>
                    </ul>
                </div>

                <!-- Research Type -->
                <div class="dropdown">
                    <button class="btn-table-action dropdown-toggle" type="button" id="dropdownResearchType"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-text"></i>
                        Type
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownResearchType">
                        <li>
                            <a class="dropdown-item" href="#">All Types</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Thesis</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Research Paper</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Project</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Journal</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Conference Paper</a>
                        </li>
                    </ul>
                </div>

                <!-- More Filters -->
                <div class="dropdown">
                    <button class="btn-table-action dropdown-toggle" type="button" id="dropdownResearchMore"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel"></i>
                        More Filters
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownResearchMore">

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person me-2"></i>
                                Author
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person-workspace me-2"></i>
                                Supervisor
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-building me-2"></i>
                                Department
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-tags me-2"></i>
                                Tags
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-check-circle me-2"></i>
                                Published
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-clock me-2"></i>
                                Pending Review
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-archive me-2"></i>
                                Archived
                            </a>
                        </li>

                    </ul>
                </div>

                <!-- Export -->
                <button class="btn-table-action" type="button">
                    <i class="bi bi-file-earmark-arrow-down"></i>
                    Export
                </button>

            </div>
        </div>


        <!-- Responsive Table -->
        <div class="table-responsive">

            <table class="table-custom">

                <thead>
                    <tr>
                        <th>Research</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Supervisor</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <!-- Research 1 -->
                    <tr>

                        <td>
                            <div>
                                <div class="table-product-name">
                                    AI-Based Student Performance Prediction System
                                </div>

                                <div class="table-user-sub">
                                    Thesis · Artificial Intelligence
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-user-cell">

                                <img src="assets/images/avatar.png" alt="Mohammad Sobuj" class="table-user-avatar">

                                <div>
                                    <div class="table-user-name">
                                        Mohammad Sobuj
                                    </div>

                                    <div class="table-user-sub">
                                        CSE · 65B
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            Artificial Intelligence
                        </td>

                        <td>
                            Computer Science
                        </td>

                        <td>
                            2026
                        </td>

                        <td>
                            Dr. Rahman
                        </td>

                        <td>
                            <span class="badge-table success">
                                Published
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">

                                <a href="#" class="table-btn-action" title="View research">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Edit research">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>

                                <a href="#" class="table-btn-action delete" title="Delete research">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>

                    </tr>


                    <!-- Research 2 -->
                    <tr>

                        <td>
                            <div>
                                <div class="table-product-name">
                                    Smart Campus Management Using IoT
                                </div>

                                <div class="table-user-sub">
                                    Research Paper · IoT
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-user-cell">

                                <img src="assets/images/avatar.png" alt="Nusrat Jahan" class="table-user-avatar">

                                <div>
                                    <div class="table-user-name">
                                        Nusrat Jahan
                                    </div>

                                    <div class="table-user-sub">
                                        CSE · 64A
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            Internet of Things
                        </td>

                        <td>
                            Computer Science
                        </td>

                        <td>
                            2025
                        </td>

                        <td>
                            Prof. Karim
                        </td>

                        <td>
                            <span class="badge-table success">
                                Published
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">

                                <a href="#" class="table-btn-action" title="View research">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Edit research">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>

                                <a href="#" class="table-btn-action delete" title="Delete research">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>

                    </tr>


                    <!-- Research 3 -->
                    <tr>

                        <td>
                            <div>
                                <div class="table-product-name">
                                    Blockchain-Based Academic Certificate Verification
                                </div>

                                <div class="table-user-sub">
                                    Thesis · Blockchain
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-user-cell">

                                <img src="assets/images/avatar.png" alt="Tanvir Hasan" class="table-user-avatar">

                                <div>
                                    <div class="table-user-name">
                                        Tanvir Hasan
                                    </div>

                                    <div class="table-user-sub">
                                        CSE · 63B
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            Blockchain
                        </td>

                        <td>
                            Computer Science
                        </td>

                        <td>
                            2024
                        </td>

                        <td>
                            Dr. Ahmed
                        </td>

                        <td>
                            <span class="badge-table pending">
                                Pending Review
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">

                                <a href="#" class="table-btn-action" title="View research">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Edit research">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>

                                <a href="#" class="table-btn-action delete" title="Delete research">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>

                    </tr>


                    <!-- Research 4 -->
                    <tr>

                        <td>
                            <div>
                                <div class="table-product-name">
                                    Cyber Security Threat Detection Using Machine Learning
                                </div>

                                <div class="table-user-sub">
                                    Research Paper · Cyber Security
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-user-cell">

                                <img src="assets/images/avatar.png" alt="Fahim Ahmed" class="table-user-avatar">

                                <div>
                                    <div class="table-user-name">
                                        Fahim Ahmed
                                    </div>

                                    <div class="table-user-sub">
                                        CSE · 62A
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            Cyber Security
                        </td>

                        <td>
                            Computer Science
                        </td>

                        <td>
                            2024
                        </td>

                        <td>
                            Prof. Hasan
                        </td>

                        <td>
                            <span class="badge-table success">
                                Published
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">

                                <a href="#" class="table-btn-action" title="View research">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Edit research">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>

                                <a href="#" class="table-btn-action delete" title="Delete research">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>

                    </tr>


                    <!-- Research 5 -->
                    <tr>

                        <td>
                            <div>
                                <div class="table-product-name">
                                    Automated Medical Image Classification
                                </div>

                                <div class="table-user-sub">
                                    Thesis · Machine Learning
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-user-cell">

                                <img src="assets/images/avatar.png" alt="Sadia Akter" class="table-user-avatar">

                                <div>
                                    <div class="table-user-name">
                                        Sadia Akter
                                    </div>

                                    <div class="table-user-sub">
                                        EEE · 65A
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            Machine Learning
                        </td>

                        <td>
                            Electrical Engineering
                        </td>

                        <td>
                            2026
                        </td>

                        <td>
                            Dr. Mahmud
                        </td>

                        <td>
                            <span class="badge-table success">
                                Published
                            </span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">

                                <a href="#" class="table-btn-action" title="View research">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Edit research">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="#" class="table-btn-action" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>

                                <a href="#" class="table-btn-action delete" title="Delete research">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <!-- Footer / Pagination -->
        <div class="table-footer-control">

            <span class="table-pagination-info">
                Showing 1 to 10 of 128 research records
            </span>

            <nav aria-label="Research library pagination">

                <ul class="pagination mb-0 gap-1">

                    <li class="page-item disabled">
                        <a class="page-link border-0" href="#">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <li class="page-item active">
                        <a class="page-link border-0" href="#">1</a>
                    </li>

                    <li class="page-item">
                        <a class="page-link border-0" href="#">2</a>
                    </li>

                    <li class="page-item">
                        <a class="page-link border-0" href="#">3</a>
                    </li>

                    <li class="page-item">
                        <a class="page-link border-0" href="#">4</a>
                    </li>

                    <li class="page-item">
                        <a class="page-link border-0" href="#">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>

                </ul>

            </nav>

        </div>

    </div>






</div>

<?php require_once("../includes/footer.php"); ?>