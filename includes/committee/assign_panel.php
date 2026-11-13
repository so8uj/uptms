<?php
/*
 * The "Assign supervisor" panel: the approved groups without a supervisor, and the approved topics without a supervisor.
 * Put it inside a page whose Alpine component is Sobuj_assignPanel (committee.js).
 */
?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="alert alert-light border py-2 small">
        Choose a supervisor for an approved group. The group starts to work with the supervisor at once and sends its topic later.
        The thesis committee signs the topic: when 2 of its members approve, it is approved.
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="table-search-box" style="max-width: 300px;">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Group, student name or ID..." x-model="search"
                @input.debounce.400ms="load()">
        </div>

        <select class="form-select-custom" style="width: auto;" x-model="departmentId" @change="departmentChanged()"
            <?= multi_department() ? '' : 'hidden' ?>>
            <option value="">All departments</option>
            <template x-for="department in filterOptions.departments" :key="department.id">
                <option :value="String(department.id)" x-text="department.name"></option>
            </template>
        </select>

        <select class="form-select-custom" style="width: auto;" x-model="batchId" @change="load()">
            <option value="">All batches</option>
            <template x-for="batch in batchOptions" :key="batch.id">
                <option :value="String(batch.id)" x-text="batch.name + ' · ' + batch.shift"></option>
            </template>
        </select>

        <button type="button" class="btn btn-link btn-sm" x-cloak x-show="hasFilters()" @click="clearFilters()">
            <i class="bi bi-x-circle me-1"></i> Clear filters
        </button>
    </div>

    <div class="card border-light shadow-sm mb-4">
        <div class="card-body">
            <h6 class="mb-3">Groups without a supervisor <span class="text-muted" x-text="'(' + groups.length + ')'"></span></h6>

            <div class="text-muted small" x-cloak x-show="!loading && groups.length === 0"
                x-text="hasFilters() ? 'No group matches these filters.' : 'Every approved group already has a supervisor.'"></div>
            <div class="text-muted small" x-show="loading">Loading...</div>

            <template x-for="team in groups" :key="team.id">
                <div class="row g-2 align-items-center border-top py-2">
                    <div class="col-12 col-lg-5">
                        <div class="fw-semibold" x-text="team.name"></div>
                        <div class="small text-muted" x-text="team.department_code + ' · ' + team.batch_name + ' · ' + team.batch_shift"></div>
                        <div class="small text-muted" x-text="members(team)"></div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <?php $pickerModel = 'groupPicks[team.id]'; $pickerEmpty = 'Choose a supervisor'; $pickerId = ''; $pickerList = 'options.teachers'; require(base_path('includes/research/supervisor_picker.php')); ?>
                    </div>

                    <div class="col-12 col-lg-2 text-lg-end">
                        <button type="button" class="btn btn-primary btn-sm" :disabled="busy === team.id" @click="assignGroup(team)">
                            <i class="bi bi-person-check me-1"></i> Assign
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="card border-light shadow-sm" x-cloak x-show="approved.length > 0">
        <div class="card-body">
            <h6 class="mb-3">Approved topics without a supervisor <span class="text-muted" x-text="'(' + approved.length + ')'"></span></h6>

            <template x-for="item in approved" :key="item.id">
                <div class="row g-2 align-items-center border-top py-2">
                    <div class="col-12 col-lg-5">
                        <div class="fw-semibold" x-text="item.title"></div>
                        <div class="small text-muted" x-text="item.code + ' · ' + (item.team_name || 'No group') + ' · ' + Sobuj_authors(item)"></div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <?php $pickerModel = 'topicPicks[item.id]'; $pickerEmpty = 'Choose a supervisor'; $pickerId = ''; $pickerList = 'options.teachers'; require(base_path('includes/research/supervisor_picker.php')); ?>
                    </div>

                    <div class="col-12 col-lg-2 text-lg-end">
                        <button type="button" class="btn btn-primary btn-sm" :disabled="busy === item.id" @click="assignTopic(item)">
                            <i class="bi bi-person-check me-1"></i> Assign
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
