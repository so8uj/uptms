<?php
// A teacher with research management opens this panel too, but only the research, batch and group/team pages.
$isAdminUser = (App\Controllers\Auth::currentUser()['role'] ?? '') === 'admin';
?>
<div class="sidebar-wrapper">

    <aside id="sidebar">

        <!-- Brand Logo -->
        <a href="<?= url($isAdminUser ? 'admin/index.php' : 'teacher/index.php') ?>" class="sidebar-brand">
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
                        <a href="<?= url($isAdminUser ? 'admin/index.php' : 'teacher/index.php') ?>"
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
                        <a href="<?= url('admin/research/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'research') ? 'active' : '' ?>">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Research Library</span>
                            <span class="sidebar-menu-badge ms-auto" data-menu-count="new_requests" hidden></span>
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
                        <a href="<?= url($isAdminUser ? 'admin/university/teachers/index.php' : 'admin/university/batches/index.php') ?>"
                            class="sidebar-menu-link <?= (in_array(current_folder(), ['departments', 'batches', 'sessions', 'teachers', 'students', 'teams'])) ? 'active' : '' ?>">
                            <i class="bi bi-building"></i>
                            <span>University</span>
                            <span class="sidebar-menu-badge ms-auto" data-menu-count="team_requests" hidden></span>
                        </a>
                    </li>

                </ul>

            </div>


            <?php if ($isAdminUser): ?>
            <!-- Evaluation -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Evaluation
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">
                        <a href="<?= url('admin/evaluation/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'evaluation') ? 'active' : '' ?>">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Evaluation</span>
                        </a>
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
                        <a href="<?= url('admin/admins/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'admins') ? 'active' : '' ?>">
                            <i class="bi bi-shield-lock"></i>
                            <span>Admins</span>
                        </a>
                    </li>

                </ul>

            </div>


            <!-- Reports -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    Reports &amp; Analytics
                </div>

                <ul class="sidebar-menu-list">

                    <li class="sidebar-menu-item">
                        <a href="<?= url('admin/reports/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'reports') ? 'active' : '' ?>">
                            <i class="bi bi-graph-up"></i>
                            <span>Reports</span>
                        </a>
                    </li>

                </ul>

            </div>


            <?php endif; ?>

            <!-- System -->
            <div class="sidebar-menu-section">

                <div class="sidebar-menu-title">
                    System
                </div>

                <ul class="sidebar-menu-list">

                    <?php if ($isAdminUser): ?>
                    <li class="sidebar-menu-item">

                        <a href="<?= url('admin/settings/index.php') ?>"
                            class="sidebar-menu-link <?= (current_folder() === 'settings') ? 'active' : '' ?>">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>

                    </li>
                    <?php else: ?>
                    <li class="sidebar-menu-item">
                        <a href="<?= url('teacher/profile.php') ?>" class="sidebar-menu-link">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <?php endif; ?>

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