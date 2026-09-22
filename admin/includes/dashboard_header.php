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
                    <img src="<?= env('ASSET_URL') ?>/admin_assets/images/avatar.png" alt="Profile Image" class="navbar-profile-img">
                    <span class="navbar-profile-name d-none d-md-inline">Mohammad Sobuj</span>
                    <i class="bi bi-chevron-down navbar-profile-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
                    <li class="dropdown-header">Welcome !</li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> My Account</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="<?= url('index.php') ?>"><i
                                class="bi bi-box-arrow-right"></i>
                            Logout</a></li>
                </ul>
            </div>
        </div>
    </header>