<?php
$me = App\Controllers\Auth::currentUser() ?? [];
$myName = $me['name'] ?? 'User';
$myRole = $me['role'] ?? '';

// "My Account" opens the page where this user's own details are edited.
$accountUrl = match ($myRole) {
    'admin'   => url('admin/admins/manage.php?action=update&id=' . (int) ($me['id'] ?? 0)),
    'teacher' => url('teacher/profile.php'),
    'student' => url('student/profile.php'),
    default   => '#',
};
?>
<header class="navbar-custom">
        <div class="navbar-left">
            <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
                id="desktop-sidebar-toggle" aria-label="Minimize Sidebar">
                <i class="bi bi-chevron-bar-left"></i>
            </button>
            <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation">
                <i class="bi bi-list"></i>
            </button>

        </div>

        <div class="navbar-search-wrapper">
            <input type="text" class="navbar-search-input" placeholder="Search anything in Spark..." id="main-search">
            <button class="navbar-search-btn" aria-label="Search">
                <i class="bi bi-search"></i>
            </button>
        </div>

        <div class="navbar-actions">
            <button class="navbar-action-btn me-1" aria-label="Toggle Fullscreen" id="btn-fullscreen">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
            

            <div class="dropdown ms-2">
                <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false" id="profile-dropdown">
                    <?php if (!empty($me['photo'])): ?>
                        <img src="<?= htmlspecialchars(url('uploads/' . $me['photo'])) ?>" alt="<?= htmlspecialchars($myName) ?>" class="navbar-profile-img">
                    <?php else: ?>
                        <div class="table-user-avatar"><?= htmlspecialchars(initials($myName)) ?></div>
                    <?php endif; ?>
                    <span class="navbar-profile-name d-none d-md-inline"><?= htmlspecialchars($myName) ?></span>
                    <i class="bi bi-chevron-down navbar-profile-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
                    <li class="dropdown-header"><?= htmlspecialchars(ucfirst($myRole)) ?></li>
                    <li><a class="dropdown-item" href="<?= $accountUrl ?>"><i class="bi bi-person"></i> My Account</a></li>
                    <?php if ($myRole === 'admin'): ?>
                        <li><a class="dropdown-item" href="<?= url('admin/settings/index.php') ?>"><i class="bi bi-gear"></i> Settings</a></li>
                    <?php endif; ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="<?= url('logout.php') ?>"><i
                                class="bi bi-box-arrow-right"></i>
                            Logout</a></li>
                </ul>
            </div>
        </div>
    </header>