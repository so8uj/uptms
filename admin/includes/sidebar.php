<div class="sidebar-wrapper">

    <aside id="sidebar">

        <!-- Brand Logo -->
        <a href="<?= url('admin/index.php') ?>" class="sidebar-brand">
            <img src="<?= env('ASSET_URL') ?>/images/wub-logo.png" alt="WORLD UNIVERSITY OF BANGLADESH" width="100%" /> 
        </a>

        <div class="flex-grow-1 overflow-y-auto sidebar-main-menu">

            <!-- Overview -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Overview
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">
                        <a href="<?= url('admin/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'admin' && current_file() === 'index.php') ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                </ul>

            </div>


            <!-- Research -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Research
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'research' && current_file() !== 'ongoing.php') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#researchLibraryMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'research' && current_file() !== 'ongoing.php') ? 'true' : 'false' ?>"
                            aria-controls="researchLibraryMenu">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Research Library</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'research' && current_file() !== 'ongoing.php') ? 'show' : '' ?>"
                            id="researchLibraryMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'research' && current_file() === 'index.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/research/index.php') ?>">
                                        Research Library
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'research' && current_file() === 'manage.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/research/manage.php?action=create') ?>">
                                        Add Thesis / Research
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'research' && current_file() === 'pending.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/research/pending.php') ?>">
                                        Pending Submissions
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'research' && current_file() === 'categories.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/research/categories.php') ?>">
                                        Research Categories
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'research' && current_file() === 'tags.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/research/tags.php') ?>">
                                        Research Tags
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                    <li class="sidebar-menu-item">
                        <a href="<?= url('admin/research/ongoing.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'research' && current_file() === 'ongoing.php') ? 'active' : '' ?>">
                            <i class="bi bi-activity"></i>
                            <span>Ongoing Research</span>
                        </a>
                    </li>

                </ul>

            </div>


            <!-- Project Management -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Project Management
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'projects') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#projectMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'projects') ? 'true' : 'false' ?>"
                            aria-controls="projectMenu">
                            <i class="bi bi-kanban"></i>
                            <span>Projects</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'projects') ? 'show' : '' ?>" id="projectMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'projects' && current_file() === 'index.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/projects/index.php') ?>">
                                        All Projects
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'projects' && current_file() === 'proposals.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/projects/proposals.php') ?>">
                                        Project Proposals
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'projects' && current_file() === 'pending.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/projects/pending.php') ?>">
                                        Pending Proposals
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'projects' && current_file() === 'milestones.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/projects/milestones.php') ?>">
                                        Milestones
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'projects' && current_file() === 'progress.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/projects/progress.php') ?>">
                                        Project Progress
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                    <li class="sidebar-menu-item">
                        <a href="<?= url('admin/teams/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'teams') ? 'active' : '' ?>">
                            <i class="bi bi-people"></i>
                            <span>Project Teams</span>
                        </a>
                    </li>

                </ul>

            </div>


            <!-- University Management -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    University Management
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (in_array(current_folder(), ['departments', 'batches', 'teachers', 'students'])) ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#universityMenu" role="button"
                            aria-expanded="<?= (in_array(current_folder(), ['departments', 'batches', 'teachers', 'students'])) ? 'true' : 'false' ?>"
                            aria-controls="universityMenu">
                            <i class="bi bi-building"></i>
                            <span>University Structure</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (in_array(current_folder(), ['departments', 'batches', 'teachers', 'students'])) ? 'show' : '' ?>"
                            id="universityMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'teachers') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/university/teachers/index.php') ?>">
                                        Teacher Management
                                    </a>
                                </li>


                                <li class="<?= (current_folder() === 'students') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/university/students/index.php') ?>">
                                        Student Management
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'departments') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/university/departments/index.php') ?>">
                                        Department Management
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'batches') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/university/batches/index.php') ?>">
                                        Batch Management
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                </ul>

            </div>


            <!-- Documents -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Project Files
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'documents') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#documentMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'documents') ? 'true' : 'false' ?>"
                            aria-controls="documentMenu">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Documents</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'documents') ? 'show' : '' ?>" id="documentMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'documents' && current_file() === 'index.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/documents/index.php') ?>">
                                        All Documents
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'documents' && current_file() === 'pending.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/documents/pending.php') ?>">
                                        Pending Documents
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                </ul>

            </div>


            <!-- Evaluation -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Evaluation
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'evaluation') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#evaluationMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'evaluation') ? 'true' : 'false' ?>"
                            aria-controls="evaluationMenu">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Evaluation</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'evaluation') ? 'show' : '' ?>"
                            id="evaluationMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'evaluation' && current_file() === 'projects.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/evaluation/projects.php') ?>">
                                        Project Evaluation
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'evaluation' && current_file() === 'thesis.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/evaluation/thesis.php') ?>">
                                        Thesis Evaluation
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'evaluation' && current_file() === 'criteria.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/evaluation/criteria.php') ?>">
                                        Evaluation Criteria
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                </ul>

            </div>


            <!-- Administration -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Administration
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'users') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#userAccessMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'users') ? 'true' : 'false' ?>"
                            aria-controls="userAccessMenu">
                            <i class="bi bi-shield-lock"></i>
                            <span>Users & Access</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'users') ? 'show' : '' ?>" id="userAccessMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'users' && current_file() === 'index.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/users/index.php') ?>">
                                        All Users
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'users' && current_file() === 'admins.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/users/admins.php') ?>">
                                        Administrators
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'users' && current_file() === 'roles.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/users/roles.php') ?>">
                                        Roles & Permissions
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                </ul>

            </div>


            <!-- Reports -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Reports & Analytics
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'reports') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#reportsMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'reports') ? 'true' : 'false' ?>"
                            aria-controls="reportsMenu">
                            <i class="bi bi-bar-chart-line"></i>
                            <span>Reports</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'reports') ? 'show' : '' ?>" id="reportsMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'reports' && current_file() === 'projects.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/reports/projects.php') ?>">
                                        Project Reports
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'reports' && current_file() === 'thesis.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/reports/thesis.php') ?>">
                                        Thesis Reports
                                    </a>
                                </li>

                                <li class="<?= (current_folder() === 'reports' && current_file() === 'research.php') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/reports/research.php') ?>">
                                        Research Reports
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                </ul>

            </div>


            <!-- System -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    System
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">

                        <a class="sidebar-menu-link <?= (current_folder() === 'settings') ? '' : 'collapsed' ?>"
                            data-bs-toggle="collapse" href="#systemMenu" role="button"
                            aria-expanded="<?= (current_folder() === 'settings') ? 'true' : 'false' ?>"
                            aria-controls="systemMenu">
                            <i class="bi bi-gear"></i>
                            <span>System Settings</span>
                            <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                        </a>

                        <div class="collapse <?= (current_folder() === 'settings') ? 'show' : '' ?>" id="systemMenu">

                            <ul class="sidebar-submenu">

                                <li class="<?= (current_folder() === 'settings') ? 'active' : '' ?>">
                                    <a href="<?= url('admin/settings/index.php') ?>">
                                        Settings
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </li>

                    <li class="sidebar-menu-item">
                        <a href="<?= url('logout.php') ?>" class="sidebar-menu-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </li>

                </ul>

            </div>

        </div>

    </aside>

</div>