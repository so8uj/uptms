<?php
require_once(__DIR__ . '/../app/bootstrap.php');

$me = App\Controllers\Auth::currentUser();

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Student Dashboard";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => "",
];

$projectUrl = url('student/project.php?id=');
$researchUrl = url('student/research/my_research.php');
$alertRole = 'student';
?>

<div class="main-wrapper" x-data="Sobuj_studentDashboard('<?= url('api/library.php') ?>', '<?= url('api/my_research.php') ?>', '<?= url('student/research/view.php?id=') ?>', '<?= url('student/research/index.php') ?>', '<?= url('api/research_options.php') ?>', '<?= url('api/project.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <p class="text-muted">Welcome, <strong><?= htmlspecialchars($me['name']) ?></strong>.</p>

    <?php require(base_path('includes/project/alerts.php')); ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Total research</div><div class="fs-4 fw-bold" x-text="counts.completed + counts.ongoing"></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Ongoing research</div><div class="fs-4 fw-bold" x-text="counts.ongoing"></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">My research requests</div><div class="fs-4 fw-bold" x-text="mineTotal"></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">My projects</div><div class="fs-4 fw-bold" x-text="projectTotal"></div></div>
        </div>
    </div>

    <div class="card border-light shadow-sm">
        <div class="card-header">
            <h5 class="card-title">Search Research</h5>
        </div>
        <div class="card-body" @click.outside="showSuggestions = false">

            <div>
                <div class="table-search-box" style="max-width: none;">
                    <i class="bi bi-search table-search-icon"></i>
                    <input type="text" class="table-search-input" placeholder="Search by title, code, tag, student or keyword..."
                        x-model="search" autocomplete="off"
                        @input.debounce.300ms="suggest()"
                        @focus="showSuggestions = suggestions.length > 0"
                        @keydown.arrow-down.prevent="move(1)"
                        @keydown.arrow-up.prevent="move(-1)"
                        @keydown.escape="showSuggestions = false"
                        @keydown.enter.prevent="submit()">
                </div>

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php require(base_path('includes/research/search_filters.php')); ?>
                </div>

                <div class="list-group mt-2" x-cloak x-show="showSuggestions">

                    <div class="list-group-item text-muted small" x-show="suggestions.length === 0">No research found for "<span x-text="search"></span>".</div>

                    <template x-for="(item, index) in suggestions" :key="item.id">
                        <a :href="'<?= url('student/research/view.php?id=') ?>' + item.id" class="list-group-item list-group-item-action"
                            :class="index === active ? 'active' : ''">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold" x-text="item.title"></div>
                                    <div class="small opacity-75" x-text="item.code + ' · ' + item.type + ' · ' + item.category_name + ' · ' + item.year"></div>
                                    <div class="small opacity-75 mt-1" x-text="overview(item)"></div>
                                </div>
                                <span class="badge-table" :class="Sobuj_statusBadge(item.status)" x-text="Sobuj_statusText(item.status)"></span>
                            </div>
                        </a>
                    </template>

                    <button type="button" class="list-group-item list-group-item-action text-primary small" x-show="suggestionTotal > suggestions.length" @click="showAll()">
                        Show all <span x-text="suggestionTotal"></span> results in the Research Library
                    </button>
                </div>
            </div>

            <div class="small text-muted mt-2">
                Type to see suggestions, choose a category or tags to narrow them, and press Enter to see all results.
                <a href="<?= url('student/research/index.php') ?>">Browse the whole library</a>
            </div>

        </div>
    </div>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/project.js?v=<?= filemtime(base_path('assets/admin_assets/js/project.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
