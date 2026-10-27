<?php
/*
 * Step 1 of Evaluations, Progress Updates, Milestones and Feedback: all my projects (theses, projects, research papers). A click opens that project.
 * Put it inside a page whose Alpine component is Sobuj_projectList (project.js).
 * The page sets $insideUrl (this page, ending with "?id=").
 */
?>

<div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

<div class="table-card-custom">

    <div class="d-flex flex-wrap gap-2 px-3 pt-3">
        <button type="button" class="filter-pill" :class="typeFilter === '' ? 'active' : ''" @click="typeFilter = ''">All</button>
        <button type="button" class="filter-pill" :class="typeFilter === 'Project' ? 'active' : ''" @click="typeFilter = 'Project'">Project</button>
        <button type="button" class="filter-pill" :class="typeFilter === 'Thesis' ? 'active' : ''" @click="typeFilter = 'Thesis'">Thesis</button>
        <button type="button" class="filter-pill" :class="typeFilter === 'Research Paper' ? 'active' : ''" @click="typeFilter = 'Research Paper'">Research Paper</button>
    </div>

    <div class="table-responsive">
        <table class="table-custom table-compact">
            <thead>
                <tr>
                    <th>Research</th>
                    <th>Group/Team</th>
                    <th>Batch</th>
                    <th>Evaluations</th>
                    <th>Progress</th>
                    <th class="text-center col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr x-cloak x-show="loading"><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
                <tr x-cloak x-show="!loading && shown().length === 0">
                    <td colspan="6" class="text-center text-muted py-4">No project found.</td>
                </tr>

                <template x-for="project in shown()" :key="project.id">
                    <tr>
                        <td style="max-width: 320px; white-space: normal;">
                            <div class="table-user-name" x-text="project.title"></div>
                            <div class="table-user-sub" x-text="project.code + ' · ' + project.type + ' · ' + Sobuj_statusText(project.status)"></div>
                        </td>
                        <td style="max-width: 230px; white-space: normal;">
                            <div class="table-user-name" x-text="project.team_name || 'No group/team'"></div>
                            <div class="table-user-sub" x-text="names(project)"></div>
                        </td>
                        <td x-text="project.batch_name || '-'"></td>
                        <td x-text="project.evaluations"></td>
                        <td style="min-width: 150px;">
                            <div class="small mb-1" x-text="project.percent + '% · ' + project.done + ' of ' + project.tasks + ' tasks'"></div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" :style="'width: ' + project.percent + '%;'"></div>
                            </div>
                        </td>
                        <td class="col-actions">
                            <div class="d-flex justify-content-end">
                                <a :href="'<?= $insideUrl ?>' + project.id" class="btn btn-primary btn-sm">
                                    <i class="bi bi-folder2-open me-1"></i> Open
                                </a>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
