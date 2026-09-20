<?php 

require_once("../includes/header.php"); 
require_once("../includes/sidebar.php"); 


$pageType = page_action() == 'create' ? "Add Thesis/Research " : "Edit Thesis/Research ";
$pageHeader = $pageType;
$showBreadcrumb = true;
$breadcrumbs = [
   "Dashboard" => url('admin/index.php'),
   "Research Library" => url('admin/research/index.php'),
    $pageType => ""
];


?>

<div class="main-wrapper">
    <?php  
      require_once("../includes/dashboard_header.php"); 
      require_once(base_path('includes/admin/page_header_breadcrumb.php')); 

      

   ?>

    <!-- Add Reseach From -->
    <form action="" method="post">

        <!-- Research Library Content -->
        <div class="row g-4">

            <!-- Thesis Information -->
            <div class="col-12">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Thesis Information</h5>

                    <div class="row g-3">

                        <!-- Thesis Title -->
                        <div class="col-12">
                            <label for="thesisTitle" class="form-label-custom">
                                Thesis Title <span class="text-danger">*</span>
                            </label>

                            <input type="text" class="form-control-custom" id="thesisTitle" name="title"
                                placeholder="Enter thesis title" required>
                        </div>

                        <!-- Research Type -->
                        <div class="col-12 col-md-6">
                            <label for="researchType" class="form-label-custom">
                                Research Type <span class="text-danger">*</span>
                            </label>

                            <select class="form-select-custom" id="researchType" name="research_type_id" required>
                                <option value="" selected disabled>
                                    Select research type
                                </option>
                                <option value="1">Thesis</option>
                                <option value="2">Research Paper</option>
                                <option value="3">Project</option>
                                <option value="4">Journal</option>
                                <option value="5">Conference Paper</option>
                            </select>
                        </div>

                        <!-- Research Category -->
                        <div class="col-12 col-md-6">
                            <label for="researchCategory" class="form-label-custom">
                                Research Category <span class="text-danger">*</span>
                            </label>

                            <select class="form-select-custom" id="researchCategory" name="category_id" required>
                                <option value="" selected disabled>
                                    Select category
                                </option>
                                <option value="1">Artificial Intelligence</option>
                                <option value="2">Machine Learning</option>
                                <option value="3">Cyber Security</option>
                                <option value="4">Internet of Things</option>
                                <option value="5">Blockchain</option>
                                <option value="6">Software Engineering</option>
                                <option value="7">Data Science</option>
                                <option value="8">Computer Networks</option>
                            </select>
                        </div>

                        <!-- Department -->
                        <div class="col-12 col-md-6">
                            <label for="department" class="form-label-custom">
                                Department <span class="text-danger">*</span>
                            </label>

                            <select class="form-select-custom" id="department" name="department_id" required>
                                <option value="" selected disabled>
                                    Select department
                                </option>
                                <option value="1">Computer Science & Engineering</option>
                                <option value="2">Electrical & Electronic Engineering</option>
                                <option value="3">Information Technology</option>
                                <option value="4">Business Administration</option>
                            </select>
                        </div>

                        <!-- Academic Session -->
                        <div class="col-12 col-md-6">
                            <label for="academicSession" class="form-label-custom">
                                Academic Session
                            </label>

                            <select class="form-select-custom" id="academicSession" name="academic_session_id">
                                <option value="" selected disabled>
                                    Select academic session
                                </option>
                                <option value="1">2025-2026</option>
                                <option value="2">2024-2025</option>
                                <option value="3">2023-2024</option>
                                <option value="4">2022-2023</option>
                            </select>
                        </div>

                        <!-- Publication Year -->
                        <div class="col-12 col-md-6">
                            <label for="publicationYear" class="form-label-custom">
                                Publication Year <span class="text-danger">*</span>
                            </label>

                            <input type="number" class="form-control-custom" id="publicationYear"
                                name="publication_year" placeholder="e.g. 2026" min="1900" max="2100" required>
                        </div>

                        <!-- Publication Date -->
                        <div class="col-12 col-md-6">
                            <label for="publicationDate" class="form-label-custom">
                                Publication Date
                            </label>

                            <input type="date" class="form-control-custom" id="publicationDate" name="publication_date">
                        </div>

                        <!-- Abstract -->
                        <div class="col-12">
                            <label for="thesisAbstract" class="form-label-custom">
                                Abstract <span class="text-danger">*</span>
                            </label>

                            <textarea class="form-control-custom" id="thesisAbstract" name="abstract" rows="6"
                                placeholder="Enter thesis abstract..." required></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Author Information -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-4">Author Information</h5>

                    <!-- Primary Author -->
                    <div class="mb-3">
                        <label for="primaryAuthor" class="form-label-custom">
                            Primary Author <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="primaryAuthor" name="primary_author_id" required>
                            <option value="" selected disabled>
                                Select primary author
                            </option>
                            <option value="1">Mohammad Sobuj</option>
                            <option value="2">Nusrat Jahan</option>
                            <option value="3">Tanvir Hasan</option>
                            <option value="4">Fahim Ahmed</option>
                            <option value="5">Sadia Akter</option>
                        </select>
                    </div>

                    <!-- Additional Authors -->
                    <div class="mb-3">
                        <label for="additionalAuthors" class="form-label-custom">
                            Additional Authors
                        </label>

                        <input type="text" class="form-control-custom" id="additionalAuthors" name="additional_authors"
                            placeholder="Enter additional author names">

                        <span class="text-muted">
                            Separate multiple authors with commas.
                        </span>
                    </div>

                    <!-- Student ID -->
                    <div class="mb-0">
                        <label for="authorStudentId" class="form-label-custom">
                            Student ID / Roll
                        </label>

                        <input type="text" class="form-control-custom" id="authorStudentId" name="student_id"
                            placeholder="Enter student ID or roll number">
                    </div>

                </div>
            </div>

            <!-- Supervisor Information -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-4">Supervisor Information</h5>

                    <!-- Supervisor -->
                    <div class="mb-3">
                        <label for="supervisor" class="form-label-custom">
                            Supervisor <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="supervisor" name="supervisor_id" required>
                            <option value="" selected disabled>
                                Select supervisor
                            </option>
                            <option value="1">Dr. Rahman</option>
                            <option value="2">Prof. Karim</option>
                            <option value="3">Dr. Ahmed</option>
                            <option value="4">Prof. Hasan</option>
                            <option value="5">Dr. Mahmud</option>
                        </select>
                    </div>

                    <!-- Co-Supervisor -->
                    <div class="mb-3">
                        <label for="coSupervisor" class="form-label-custom">
                            Co-Supervisor
                        </label>

                        <select class="form-select-custom" id="coSupervisor" name="co_supervisor_id">
                            <option value="" selected>
                                No co-supervisor
                            </option>
                            <option value="1">Dr. Rahman</option>
                            <option value="2">Prof. Karim</option>
                            <option value="3">Dr. Ahmed</option>
                            <option value="4">Prof. Hasan</option>
                        </select>
                    </div>

                    <!-- Research Area -->
                    <div class="mb-0">
                        <label for="researchArea" class="form-label-custom">
                            Research Area
                        </label>

                        <input type="text" class="form-control-custom" id="researchArea" name="research_area"
                            placeholder="e.g. Machine Learning, NLP">
                    </div>

                </div>
            </div>

            <!-- Research Details -->
            <div class="col-12">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Research Details</h5>

                    <div class="row g-3">

                        <!-- Keywords -->
                        <div class="col-12 col-md-6">
                            <label for="keywords" class="form-label-custom">
                                Keywords
                            </label>

                            <input type="text" class="form-control-custom" id="keywords" name="keywords"
                                placeholder="e.g. AI, Machine Learning, Prediction">

                            <span class="text-muted">
                                Separate keywords with commas.
                            </span>
                        </div>

                        <!-- Research Tags -->
                        <div class="col-12 col-md-6">
                            <label for="tags" class="form-label-custom">
                                Research Tags
                            </label>

                            <input type="text" class="form-control-custom" id="tags" name="tags"
                                placeholder="e.g. AI, Education, Prediction">

                            <span class="text-muted">
                                Separate tags with commas.
                            </span>
                        </div>

                        <!-- Research Methodology -->
                        <div class="col-12">
                            <label for="methodology" class="form-label-custom">
                                Research Methodology
                            </label>

                            <textarea class="form-control-custom" id="methodology" name="methodology" rows="5"
                                placeholder="Describe the research methodology..."></textarea>
                        </div>

                        <!-- Research Objectives -->
                        <div class="col-12">
                            <label for="objectives" class="form-label-custom">
                                Research Objectives
                            </label>

                            <textarea class="form-control-custom" id="objectives" name="objectives" rows="5"
                                placeholder="Enter the main research objectives..."></textarea>
                        </div>

                        <!-- Research Findings -->
                        <div class="col-12">
                            <label for="findings" class="form-label-custom">
                                Research Findings
                            </label>

                            <textarea class="form-control-custom" id="findings" name="findings" rows="5"
                                placeholder="Enter a summary of the research findings..."></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Thesis Document -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-4">Thesis Document</h5>

                    <!-- Thesis PDF -->
                    <div class="mb-3">
                        <label for="thesisFile" class="form-label-custom">
                            Thesis PDF <span class="text-danger">*</span>
                        </label>

                        <input type="file" class="form-control-custom" id="thesisFile" name="thesis_file" accept=".pdf"
                            required>

                        <span class="text-muted">
                            Upload the complete thesis document in PDF format.
                        </span>
                    </div>

                    <!-- Cover Image -->
                    <div class="mb-0">
                        <label for="coverImage" class="form-label-custom">
                            Cover Image
                        </label>

                        <input type="file" class="form-control-custom" id="coverImage" name="cover_image"
                            accept="image/*">

                        <span class="text-muted">
                            Optional. Recommended formats: JPG, PNG or WebP.
                        </span>
                    </div>

                </div>
            </div>

            <!-- Publication & Status -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-4">Publication &amp; Status</h5>

                    <!-- Status -->
                    <div class="mb-3">
                        <label for="thesisStatus" class="form-label-custom">
                            Status <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="thesisStatus" name="status" required>
                            <option value="" selected disabled>
                                Select status
                            </option>
                            <option value="draft">Draft</option>
                            <option value="pending_review">Pending Review</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <!-- Library Visibility -->
                    <div class="mb-3">
                        <label for="visibility" class="form-label-custom">
                            Library Visibility
                        </label>

                        <select class="form-select-custom" id="visibility" name="visibility">
                            <option value="private">
                                Private
                            </option>
                            <option value="university">
                                University Members
                            </option>
                            <option value="public" selected>
                                Public
                            </option>
                        </select>
                    </div>

                    <!-- Citation -->
                    <div class="mb-0">
                        <label for="citation" class="form-label-custom">
                            Citation / Reference
                        </label>

                        <textarea class="form-control-custom" id="citation" name="citation" rows="4"
                            placeholder="Enter citation information if available..."></textarea>
                    </div>

                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-12">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Additional Information</h5>

                    <div class="row g-3">

                        <!-- Additional Description -->
                        <div class="col-12">
                            <label for="description" class="form-label-custom">
                                Additional Description
                            </label>

                            <textarea class="form-control-custom" id="description" name="description" rows="5"
                                placeholder="Enter any additional information about this thesis..."></textarea>
                        </div>

                        <!-- Admin Notes -->
                        <div class="col-12">
                            <label for="adminNotes" class="form-label-custom">
                                Admin Notes
                            </label>

                            <textarea class="form-control-custom" id="adminNotes" name="admin_notes" rows="4"
                                placeholder="Internal notes about this research..."></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="col-12">
                <div class="card border-light shadow-sm p-4">

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">

                        <div>
                            <span class="text-muted">
                                Fields marked with
                                <span class="text-danger">*</span>
                                are required.
                            </span>
                        </div>

                        <div class="d-flex gap-2">

                            <a href="research-library.php" class="btn btn-light">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Create Thesis
                            </button>

                        </div>

                    </div>

                </div>
            </div>

        </div>

    </form>



</div>

<?php require_once("../includes/footer.php"); ?>