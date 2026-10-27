<?php
/*
 * "What is new": what other people did in my research since I last looked.
 * Put it inside a dashboard whose Alpine component has the list "alerts" (Sobuj_projectList or Sobuj_studentDashboard).
 * The page sets $projectUrl (the project page, ending with "?id="), $researchUrl (the My Research page) and $alertRole (teacher | student).
 */
?>

<div class="card border-warning shadow-sm mb-4" x-cloak x-show="alerts.length > 0">
    <div class="card-header">
        <h5 class="card-title"><i class="bi bi-bell me-2"></i>What is new</h5>
        <span class="small text-muted" x-text="alerts.length + (alerts.length === 1 ? ' research' : ' research')"></span>
    </div>
    <div class="list-group list-group-flush">
        <template x-for="item in alerts" :key="item.research_id">
            <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-semibold" x-text="item.title"></div>
                    <template x-for="line in Sobuj_alertLines(item, '<?= $alertRole ?>')">
                        <div class="small text-muted"><i class="bi bi-dot"></i><span x-text="line"></span></div>
                    </template>
                </div>
                <a :href="['ongoing', 'completed'].includes(item.status) ? '<?= $projectUrl ?>' + item.research_id : '<?= $researchUrl ?>'"
                    class="btn btn-sm btn-primary text-nowrap">Open</a>
            </div>
        </template>
    </div>
</div>
