<div class="sidebar-wrapper">

    <aside id="sidebar">

        <a href="<?= url('teacher/index.php') ?>" class="sidebar-brand">
            <img src="<?= env('ASSET_URL') ?>/images/wub-logo.png" alt="WORLD UNIVERSITY OF BANGLADESH" width="100%">
        </a>

        <div class="flex-grow-1 overflow-y-auto sidebar-main-menu">

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Overview</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/index.php') ?>" class="sidebar-menu-link <?= (current_folder() === 'teacher' && current_file() === 'index.php') ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Supervision</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/projects.php') ?>" class="sidebar-menu-link <?= (current_file() === 'projects.php') ? 'active' : '' ?>">
                            <i class="bi bi-kanban"></i>
                            <span>Assigned Projects</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/proposals.php') ?>" class="sidebar-menu-link <?= (current_file() === 'proposals.php') ? 'active' : '' ?>">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Project Proposals</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/milestones.php') ?>" class="sidebar-menu-link <?= (current_file() === 'milestones.php') ? 'active' : '' ?>">
                            <i class="bi bi-flag"></i>
                            <span>Milestones</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/progress.php') ?>" class="sidebar-menu-link <?= (current_file() === 'progress.php') ? 'active' : '' ?>">
                            <i class="bi bi-activity"></i>
                            <span>Progress Updates</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Reviews</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/documents.php') ?>" class="sidebar-menu-link <?= (current_file() === 'documents.php') ? 'active' : '' ?>">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>Documents</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/feedback.php') ?>" class="sidebar-menu-link <?= (current_file() === 'feedback.php') ? 'active' : '' ?>">
                            <i class="bi bi-chat-left-text"></i>
                            <span>Feedback</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/evaluations.php') ?>" class="sidebar-menu-link <?= (current_file() === 'evaluations.php') ? 'active' : '' ?>">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Evaluations</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Research</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/research/index.php') ?>" class="sidebar-menu-link <?= (current_folder() === 'research' && current_file() === 'index.php') ? 'active' : '' ?>">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Research Library</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/research/my_research.php') ?>" class="sidebar-menu-link <?= (current_folder() === 'research' && current_file() === 'my_research.php') ? 'active' : '' ?>">
                            <i class="bi bi-folder2-open"></i>
                            <span>My Research</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/reports.php') ?>" class="sidebar-menu-link <?= (current_file() === 'reports.php') ? 'active' : '' ?>">
                            <i class="bi bi-bar-chart-line"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Account</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/profile.php') ?>" class="sidebar-menu-link <?= (current_file() === 'profile.php') ? 'active' : '' ?>">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('index.php') ?>" class="sidebar-menu-link">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

    </aside>

</div>
