<?php
/*
 * The small box of the Groups/Teams list: the reason of a rejection, or the question before a delete.
 * Put it inside a page whose Alpine component is Sobuj_teamList (team.js). Approve has no box.
 */
?>

<div x-cloak x-show="box.open" class="position-fixed top-0 start-0 w-100 h-100"
    style="background: rgba(0, 0, 0, 0.45); z-index: 2000;" @keydown.escape.window="closeBox()">

    <div class="card shadow p-4 position-absolute top-50 start-50 translate-middle"
        style="max-width: 460px; width: calc(100% - 2rem);">

        <h5 class="mb-1" x-text="boxTitle"></h5>
        <div class="text-muted small mb-3" x-text="box.item ? box.item.name : ''"></div>

        <div x-show="boxNeedsReason">
            <label for="boxNote" class="form-label-custom">Reason <span class="text-danger">*</span></label>
            <textarea class="form-control-custom" id="boxNote" rows="3" maxlength="500" placeholder="Write why. The students will read it."
                x-model="box.note" @input="box.error = ''"></textarea>
            <div class="text-danger small mt-1" x-text="box.error" x-show="box.error"></div>
        </div>

        <p class="mb-0" x-show="box.kind === 'delete'">This group/team will be deleted. This cannot be undone.</p>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light" @click="closeBox()">Cancel</button>
            <button type="button" class="btn btn-danger" :disabled="box.saving" @click="confirmBox()">
                <span x-text="box.kind === 'delete' ? 'Delete' : 'Reject'"></span>
            </button>
        </div>
    </div>
</div>
