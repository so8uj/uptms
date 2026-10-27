<?php

require_once(__DIR__ . '/../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "My Group/Team";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "My Group/Team" => ""
];

$meId = (int) (App\Controllers\Auth::currentUser()['id'] ?? 0);

?>

<div class="main-wrapper" x-data="Sobuj_studentTeam('<?= url('api/teams.php') ?>', <?= $meId ?>, '<?= url('student/project.php?id=') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div x-cloak x-show="loading" class="text-center text-muted py-5">Loading...</div>

    <div x-cloak x-show="!loading">

        <!-- my group/team -->
        <div class="card border-light shadow-sm" x-show="team && mode !== 'create'">
            <div class="card-body p-4" x-show="team">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="mb-1" x-text="team ? team.name : ''"></h5>
                        <div class="text-muted small" x-text="team ? team.department_name + ' · ' + team.batch_name + ' · ' + team.batch_shift : ''"></div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <div class="small text-muted mb-1">Group/Team status</div>
                        <span class="badge-table" :class="statusBadge(team ? team.status : '')" x-text="statusText(team ? team.status : '')"></span>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="small text-muted mb-1">Project status</div>
                        <span class="badge-table" :class="team && team.research_status ? 'success' : 'pending'" x-text="projectText()"></span>
                    </div>
                </div>

                <div class="alert alert-info py-2" x-show="team && team.status === 'requested'">
                    Your group/team request is waiting for the administrator. You can request a topic after it is approved.
                </div>

                <div class="alert alert-info py-2" x-show="team && team.status === 'open' && team.change && team.change.status === 'pending'">
                    <strong>Member change waiting for the administrator.</strong>
                    <template x-for="student in (team && team.change ? team.change.added : [])" :key="'a' + student.id">
                        <div class="small">+ <span x-text="student.name"></span> (add)</div>
                    </template>
                    <template x-for="student in (team && team.change ? team.change.removed : [])" :key="'r' + student.id">
                        <div class="small">- <span x-text="student.name"></span> (remove)</div>
                    </template>
                </div>

                <div class="alert alert-warning py-2" x-show="team && team.status === 'open' && team.change && team.change.status === 'rejected'">
                    <strong>Your member change was rejected:</strong> <span x-text="team && team.change ? team.change.review_note : ''"></span><br>
                    <span class="small">A group/team can ask for a member change only once, so you cannot ask again. Please contact the administrator.</span>
                </div>

                <div class="alert alert-success py-2" x-show="team && team.status === 'open' && team.change && team.change.status === 'approved'">
                    Your member change was approved. A group/team can ask for a member change only once, so you cannot ask again.
                </div>

                <div class="alert alert-danger py-2" x-show="team && team.status === 'rejected'">
                    <strong>Rejected:</strong> <span x-text="team ? team.review_note : ''"></span>
                </div>

                <div class="mb-3" x-show="team && ['open', 'closed'].includes(team.status)">
                    <div class="small text-muted">Project</div>
                    <template x-if="team && team.research_id">
                        <div>
                            <span class="fw-semibold" x-text="team.research_title"></span>
                            <span class="text-muted small" x-text="' · ' + team.research_code"></span>
                            <div><a :href="researchLink()" class="small" x-show="['ongoing', 'completed'].includes(team.research_status)">Open project work</a></div>
                        </div>
                    </template>
                    <div x-show="team && !team.research_id && team.status === 'open'" class="small text-muted">
                        No project yet. Your group/team can <a href="<?= url('student/research/manage.php?action=create') ?>">request a topic</a> now.
                    </div>
                </div>

                <!-- the members -->
                <div x-show="mode === 'view'">
                    <div class="small text-muted mb-1">Members (<span x-text="team ? team.members.length : 0"></span>)</div>
                    <ul class="list-unstyled mb-3">
                        <template x-for="member in (team ? team.members : [])" :key="member.id">
                            <li class="d-flex align-items-center gap-2 py-1">
                                <div class="table-user-avatar" x-text="Sobuj_initials(member.name)"></div>
                                <div>
                                    <div class="table-user-name" x-text="member.name"></div>
                                    <div class="table-user-sub" x-text="member.student_id || ''"></div>
                                </div>
                            </li>
                        </template>
                    </ul>

                    <div x-show="team && team.status === 'open'">
                        <button type="button" class="btn btn-primary" @click="openEdit()" x-show="!changeUsed">
                            <i class="bi bi-people me-1"></i> Request a member change
                        </button>
                        <div class="small text-muted mt-2">
                            A group/team keeps <span x-text="minSize"></span> to <span x-text="maxSize"></span> members. Until the project is completed,
                            the group/team cannot be left or dissolved. A change of members is a request, the administrator decides, and a group/team can ask only once.
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" x-show="team && team.status === 'rejected'" @click="openCreate()">
                        <i class="bi bi-plus-circle me-1"></i> Request a new group/team
                    </button>
                </div>

                <!-- change the members -->
                <form x-show="mode === 'edit'" @submit.prevent="save()" novalidate>
                    <?php require(base_path('includes/team/picker.php')); ?>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary" :disabled="saving || sizeProblem() !== ''">Send request</button>
                        <button type="button" class="btn btn-light" @click="cancel()">Cancel</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- I have no group/team -->
        <div class="card border-light shadow-sm" x-show="!team && mode !== 'create'">
            <div class="card-body p-4">
                <h5 class="mb-2">You are not in a group/team</h5>
                <p class="text-muted mb-3">
                    A research is done by a group/team of <span x-text="minSize"></span> to <span x-text="maxSize"></span> students of your batch.
                    Send a request and the administrator will approve it, or the administrator can add you to one.
                </p>
                <button type="button" class="btn btn-primary" @click="openCreate()">
                    <i class="bi bi-plus-circle me-1"></i> Request a group/team
                </button>
            </div>
        </div>

        <!-- ask for a group/team -->
        <div class="card border-light shadow-sm" x-show="mode === 'create'">
            <div class="card-body p-4">
                <h5 class="mb-3">Request a group/team</h5>

                <form @submit.prevent="save()" novalidate>
                    <div class="mb-3">
                        <label for="teamName" class="form-label-custom">Group/Team name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control-custom" id="teamName" maxlength="100" placeholder="For example Team Nova" x-model="form.name">
                        <template x-for="message in (errors.name || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <?php require(base_path('includes/team/picker.php')); ?>
                        <small class="text-muted d-block mt-1">You are always a member of the group/team you request.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="saving || sizeProblem() !== ''">Send request</button>
                        <button type="button" class="btn btn-light" @click="cancel()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/users.js?v=<?= filemtime(base_path('assets/admin_assets/js/users.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/team.js?v=<?= filemtime(base_path('assets/admin_assets/js/team.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
