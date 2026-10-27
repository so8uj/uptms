<div class="sidebar-wrapper">

    <aside id="sidebar">

        <a href="<?= url('student/index.php') ?>" class="sidebar-brand">
            <img src="<?= env('ASSET_URL') ?>/images/wub-logo.png" alt="WORLD UNIVERSITY OF BANGLADESH" width="100%">
        </a>

        <div class="flex-grow-1 overflow-y-auto sidebar-main-menu">

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Overview</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/index.php') ?>" class="sidebar-menu-link <?= (current_folder() === 'student' && current_file() === 'index.php') ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Research</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/research/index.php') ?>" class="sidebar-menu-link <?= (current_folder() === 'research' && current_file() === 'index.php') ? 'active' : '' ?>">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Research Library</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Project Work</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/team.php') ?>" class="sidebar-menu-link <?= (current_file() === 'team.php') ? 'active' : '' ?>">
                            <i class="bi bi-people"></i>
                            <span>My Group/Team</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/research/my_research.php') ?>" class="sidebar-menu-link <?= (current_file() === 'project.php' || (current_folder() === 'research' && in_array(current_file(), ['my_research.php', 'manage.php'], true))) ? 'active' : '' ?>">
                            <i class="bi bi-folder2-open"></i>
                            <span>My Research</span>
                            <span class="sidebar-menu-badge" data-menu-count="requested" hidden></span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/milestones.php') ?>" class="sidebar-menu-link <?= (current_file() === 'milestones.php') ? 'active' : '' ?>">
                            <i class="bi bi-flag"></i>
                            <span>Milestones</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/progress.php') ?>" class="sidebar-menu-link <?= (current_file() === 'progress.php') ? 'active' : '' ?>">
                            <i class="bi bi-activity"></i>
                            <span>Progress Updates</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/feedback.php') ?>" class="sidebar-menu-link <?= (current_file() === 'feedback.php') ? 'active' : '' ?>">
                            <i class="bi bi-chat-left-text"></i>
                            <span>Feedback</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/evaluations.php') ?>" class="sidebar-menu-link <?= (current_file() === 'evaluations.php') ? 'active' : '' ?>">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Evaluations</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-title">Account</div>
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="<?= url('student/profile.php') ?>" class="sidebar-menu-link <?= (current_file() === 'profile.php') ? 'active' : '' ?>">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
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
