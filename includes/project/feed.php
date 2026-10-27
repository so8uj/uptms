<?php
/*
 * One list across all my projects (milestones, progress updates, feedback or evaluations).
 * Put it inside a page whose Alpine component is Sobuj_projectFeed (project.js).
 * The page sets $view (milestones | comments | evaluations | activity) and $projectUrl (the project page, ending with "?id=").
 * $items = the name of the Alpine list (default "items"; the page of one project has lists.evaluations and so on).
 */
?>

<div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

<div class="table-card-custom">

    <?php if ($view === 'milestones'): ?>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Milestone</th>
                        <th x-show="!single">Research</th>
                        <th>Due</th>
                        <th>Progress</th>
                        <th>Made by</th>
                    </tr>
                </thead>
                <tbody>
                    <tr x-cloak x-show="loading"><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
                    <tr x-cloak x-show="!loading && <?= $items ?? 'items' ?>.length === 0"><td colspan="5" class="text-center text-muted py-4">No milestone yet. Milestones are made on the project page.</td></tr>
                    <template x-for="item in <?= $items ?? 'items' ?>" :key="item.id">
                        <tr>
                            <td style="max-width: 280px; white-space: normal;">
                                <div class="table-user-name" x-text="item.title"></div>
                                <div class="table-user-sub" x-show="item.description" x-text="item.description"></div>
                            </td>
                            <td style="max-width: 260px; white-space: normal;" x-show="!single">
                                <a :href="'<?= $projectUrl ?>' + item.research_id" x-text="item.research_title"></a>
                                <div class="table-user-sub" x-text="item.research_code"></div>
                            </td>
                            <td x-text="item.due_date ? Sobuj_formatDate(item.due_date) : '-'"></td>
                            <td style="min-width: 150px;">
                                <div class="small mb-1" x-text="percent(item) + '% · ' + item.done + ' of ' + item.tasks + ' tasks'"></div>
                                <div class="progress" style="height: 6px;"><div class="progress-bar bg-success" :style="'width: ' + percent(item) + '%;'"></div></div>
                            </td>
                            <td x-text="item.creator_name"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'evaluations'): ?>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th x-show="!single">Research</th>
                        <th>Rating</th>
                        <th>Report</th>
                        <th>By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr x-cloak x-show="loading"><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
                    <tr x-cloak x-show="!loading && <?= $items ?? 'items' ?>.length === 0"><td colspan="5" class="text-center text-muted py-4">No evaluation yet. The supervisor writes them on the project page.</td></tr>
                    <template x-for="item in <?= $items ?? 'items' ?>" :key="item.id">
                        <tr>
                            <td style="max-width: 260px; white-space: normal;" x-show="!single">
                                <a :href="'<?= $projectUrl ?>' + item.research_id" x-text="item.research_title"></a>
                                <div class="table-user-sub" x-text="item.research_code"></div>
                            </td>
                            <td class="fw-semibold" x-text="item.rating + ' of 5 · ' + Sobuj_ratingText(item.rating)"></td>
                            <td style="max-width: 360px; white-space: pre-line;" x-text="item.remarks"></td>
                            <td x-text="item.teacher_name"></td>
                            <td x-text="Sobuj_formatDate(item.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'comments'): ?>

        <div class="list-group list-group-flush">
            <div class="list-group-item text-center text-muted py-4" x-show="loading">Loading...</div>
            <div class="list-group-item text-center text-muted py-4" x-show="!loading && <?= $items ?? 'items' ?>.length === 0">No comment yet. Comments are written on the project page.</div>
            <template x-for="item in <?= $items ?? 'items' ?>" :key="item.id">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <span class="fw-semibold" x-text="who(item)"></span>
                            <span class="text-muted small"> on </span>
                            <a :href="'<?= $projectUrl ?>' + item.research_id" x-text="item.research_title"></a>
                        </div>
                        <span class="small text-muted text-nowrap" x-text="item.created_at.substring(0, 16)"></span>
                    </div>
                    <div class="mt-1" style="white-space: pre-line;" x-text="item.body"></div>
                </div>
            </template>
        </div>

    <?php else: ?>

        <div class="list-group list-group-flush">
            <div class="list-group-item text-center text-muted py-4" x-show="loading">Loading...</div>
            <div class="list-group-item text-center text-muted py-4" x-show="!loading && <?= $items ?? 'items' ?>.length === 0">Nothing has happened yet.</div>
            <template x-for="item in <?= $items ?? 'items' ?>" :key="item.id">
                <div class="list-group-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2"><i class="bi" :class="Sobuj_activityIcon(item.action)"></i></div>
                        <div class="flex-grow-1">
                            <div>
                                <span class="fw-semibold" x-text="who(item)"></span>
                                <span x-text="Sobuj_activityText(item)"></span>
                                <span class="text-muted small" x-show="!single"> in </span>
                                <a :href="'<?= $projectUrl ?>' + item.research_id" x-show="!single" x-text="item.research_title"></a>
                            </div>
                            <div class="small mt-1 p-2 rounded bg-light" x-show="Sobuj_activityNote(item)" x-text="Sobuj_activityNote(item)"></div>
                            <div class="small text-muted mt-1" x-text="item.created_at.substring(0, 16)"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

    <?php endif; ?>

</div>
