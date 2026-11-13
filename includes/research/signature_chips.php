<?php
/*
 * Who must sign a research and who signed. Put it inside a loop whose Alpine variable has signers, needed and signed
 * (the rows of api/research.php and api/committee.php).
 * The page sets $sigItem: the name of the variable, for example 'item' or 'request'.
 */
?>
<div class="small">
    <div class="fw-semibold" x-text="<?= $sigItem ?>.signed + ' of ' + <?= $sigItem ?>.needed + ' signatures'"></div>

    <template x-for="teacher in <?= $sigItem ?>.signers" :key="teacher.teacher_id">
        <span class="badge-table me-1 mt-1" :class="{ approve: 'success', reject: 'failed', correction: 'pending' }[teacher.decision] || 'pending'"
            x-text="teacher.name + ({ approve: ' · signed', reject: ' · rejected', correction: ' · correction' }[teacher.decision] || ' · waiting')"></span>
    </template>

    <div class="text-muted" x-show="<?= $sigItem ?>.signers.length === 0">Nobody can sign yet. The admin must add committee members who can approve.</div>
</div>
