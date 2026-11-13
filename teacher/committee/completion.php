<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));

$pageHeader = "Completion Requests";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('teacher/index.php'),
    "Thesis Committee" => "",
    "Completion Requests" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_completionRequests('<?= url('api/committee.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <p class="text-muted">These research were finished by the team. The supervisor approved and every team member confirmed. When 2 of the members who approve sign, the research is completed.</p>

    <div class="text-center text-muted py-4" x-cloak x-show="loading">Loading...</div>

    <div class="card border-light shadow-sm" x-cloak x-show="!loading && requests.length === 0">
        <div class="card-body text-center text-muted">No request is waiting for you.</div>
    </div>

    <template x-for="request in requests" :key="request.id">
        <div class="card border-light shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted" x-text="request.code"></div>
                        <h5 class="mb-1" x-text="request.title"></h5>
                        <div class="small text-muted" x-text="'Asked by ' + request.requested_by_name + ' on ' + request.created_at.substring(0, 10)"></div>
                    </div>
                    <a :href="'<?= url('teacher/committee/view.php?id=') ?>' + request.research_id" class="btn btn-sm btn-light">Details</a>
                </div>

                <div class="mt-2">
                    <div class="small text-muted">Signatures</div>
                    <?php $sigItem = 'request'; require(base_path('includes/research/signature_chips.php')); ?>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-4">
                        <div class="small text-muted">Supervisor</div>
                        <div class="fw-semibold" x-text="request.supervisor_name || '-'"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="small text-muted">Team (all confirmed)</div>
                        <div class="fw-semibold" x-text="request.team.join(', ')"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="small text-muted">Work</div>
                        <div class="fw-semibold" x-text="request.done + ' of ' + request.tasks + ' tasks done, ' + request.milestones + ' milestones'"></div>
                    </div>
                </div>

                <div class="mt-3 small text-muted" x-show="!request.can_sign">You are not one of the members who approve.</div>
                <div class="small text-primary mt-2" x-show="request.can_sign && request.my_vote" x-text="'You signed: ' + { approve: 'approved', reject: 'rejected' }[request.my_vote]"></div>

                <div class="mt-3" x-show="request.can_sign && rejecting !== request.id">
                    <button type="button" class="btn btn-primary btn-sm" @click="decide(request, 'approve')">Sign: complete this research</button>
                    <button type="button" class="btn btn-light btn-sm" @click="openReject(request)">Reject</button>
                </div>

                <div class="mt-3" x-show="request.can_sign && rejecting === request.id">
                    <label class="form-label-custom">Reason (the team and the supervisor will see it) <span class="text-danger">*</span></label>
                    <textarea class="form-control-custom mb-1" rows="2" maxlength="500" x-model="note"></textarea>
                    <template x-for="message in (errors.note || [])">
                        <div class="text-danger small mb-1" x-text="message"></div>
                    </template>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-danger btn-sm" @click="decide(request, 'reject')">Reject request</button>
                        <button type="button" class="btn btn-light btn-sm" @click="rejecting = null">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
