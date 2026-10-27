<?php
/*
 * One project with tabs: Evaluations | Progress updates | Milestones | Feedback.
 * Put it inside a page whose Alpine component is Sobuj_projectInside (project.js).
 * The page sets $backUrl (the list of projects), $workUrl (the project page, ending with "?id=") and $researchId.
 */
?>

<div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" x-cloak x-show="research">
    <div>
        <div class="fw-semibold" x-text="research ? research.title : ''"></div>
        <div class="small text-muted"
            x-text="research ? research.code + ' · ' + (research.team_name || 'No group/team') + (research.batch_name ? ' · Batch ' + research.batch_name : '') : ''"></div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $backUrl ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> All projects</a>
        <a href="<?= $workUrl . $researchId ?>" class="btn btn-primary"><i class="bi bi-kanban me-1"></i> Open the project</a>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <button type="button" class="filter-pill" :class="tab === 'evaluations' ? 'active' : ''" @click="tab = 'evaluations'">
        Evaluations <span x-text="'(' + lists.evaluations.length + ')'"></span>
    </button>
    <button type="button" class="filter-pill" :class="tab === 'activity' ? 'active' : ''" @click="tab = 'activity'">
        Progress updates <span x-text="'(' + lists.activity.length + ')'"></span>
    </button>
    <button type="button" class="filter-pill" :class="tab === 'milestones' ? 'active' : ''" @click="tab = 'milestones'">
        Milestones <span x-text="'(' + lists.milestones.length + ')'"></span>
    </button>
    <button type="button" class="filter-pill" :class="tab === 'comments' ? 'active' : ''" @click="tab = 'comments'">
        Feedback <span x-text="'(' + lists.comments.length + ')'"></span>
    </button>
</div>

<?php $projectUrl = $workUrl; ?>

<div x-show="tab === 'evaluations'">
    <?php $view = 'evaluations'; $items = 'lists.evaluations'; require(base_path('includes/project/feed.php')); ?>
</div>
<div x-cloak x-show="tab === 'activity'">
    <?php $view = 'activity'; $items = 'lists.activity'; require(base_path('includes/project/feed.php')); ?>
</div>
<div x-cloak x-show="tab === 'milestones'">
    <?php $view = 'milestones'; $items = 'lists.milestones'; require(base_path('includes/project/feed.php')); ?>
</div>
<div x-cloak x-show="tab === 'comments'">
    <?php $view = 'comments'; $items = 'lists.comments'; require(base_path('includes/project/feed.php')); ?>
</div>
