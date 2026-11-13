<?php
/*
 * The box where the admin approves, rejects or assigns a supervisor to a research.
 * Put it inside a page whose Alpine component is Sobuj_researchAdminList (research.js).
 */
?>

    <div x-cloak x-show="review.open" class="position-fixed top-0 start-0 w-100 h-100"
        style="background: rgba(0, 0, 0, 0.45); z-index: 2000;" @keydown.escape.window="closeReview()">

        <div class="card shadow p-4 position-absolute top-50 start-50 translate-middle"
            style="max-width: 560px; width: calc(100% - 2rem); max-height: 92vh; overflow-y: auto;">

            <template x-if="review.item">
                <div>
                    <div class="small text-muted" x-text="review.item.code + ' · ' + review.item.type + ' · ' + review.item.category_name"></div>
                    <h5 class="mb-2" x-text="review.item.title"></h5>
                    <p class="text-muted small mb-1" x-text="review.item.abstract"></p>
                    <div class="small mb-3">Submitted by <strong x-text="review.item.creator_name"></strong>,
                        wants it as <strong x-text="Sobuj_statusText(review.item.target_status)"></strong>.
                        <span x-show="review.item.supervisor_name">Supervisor: <strong x-text="review.item.supervisor_name"></strong>.</span></div>
                </div>
            </template>

            <div class="alert alert-light border py-2 small" x-show="review.item && review.item.status !== 'approved'">
                You sign as a committee member who can approve. The topic is approved when <strong x-text="review.item ? review.item.needed : 2"></strong> committee members approve it.
                One correction sends it back, and <span x-text="review.item ? review.item.needed : 2"></span> rejects refuse it. You can change your signature until it is decided.
            </div>

            <div class="btn-group w-100 mb-3" role="group" x-show="review.item && review.item.status !== 'approved'">
                <button type="button" class="btn" :class="review.decision === 'approve' ? 'btn-success' : 'btn-light'"
                    @click="review.decision = 'approve'; review.errors = {}">Approve</button>
                <button type="button" class="btn" :class="review.decision === 'correction' ? 'btn-warning' : 'btn-light'"
                    @click="review.decision = 'correction'; review.errors = {}">Correction</button>
                <button type="button" class="btn" :class="review.decision === 'reject' ? 'btn-danger' : 'btn-light'"
                    @click="review.decision = 'reject'; review.errors = {}">Reject</button>
            </div>

            <div class="alert alert-warning py-2 small" x-show="review.decision === 'correction'">
                <strong>Correction:</strong> it goes back to the owner with your note. The owner can edit it and send it again.
            </div>
            <div class="alert alert-danger py-2 small" x-show="review.decision === 'reject' && review.item && review.item.supervisor_id">
                <strong>Reject:</strong> the topic is refused. The group keeps its supervisor and can send a new topic.
            </div>
            <div class="alert alert-danger py-2 small" x-show="review.decision === 'reject' && review.item && !review.item.supervisor_id">
                <strong>Reject:</strong> final. The research is refused for good and nothing more can be done with it.
            </div>

            <div x-show="review.decision === 'approve' && review.item && review.item.status === 'requested' && review.item.supervisor_id" class="alert alert-info py-2 small">
                The topic is approved and the research becomes <strong>Ongoing</strong> with the supervisor the group already has.
            </div>
            <div x-show="review.decision === 'approve' && review.item && review.item.status === 'requested' && !review.item.supervisor_id" class="alert alert-info py-2 small">
                The topic becomes <strong>Approved</strong>. The student then adds the team members, and a supervisor is assigned.
            </div>

            <div x-show="review.decision === 'assign'">

                <template x-if="review.item">
                    <div class="small mb-3">
                        Team (<span x-text="review.item.students.length"></span>):
                        <strong x-text="Sobuj_authors(review.item)"></strong>
                        <div class="text-danger" x-show="review.item.students.length < 2">The student must add at least 1 team member before a supervisor can be assigned.</div>
                    </div>
                </template>

                <div class="mb-3">
                    <label for="assignSupervisor" class="form-label-custom">Supervisor <span class="text-danger">*</span></label>
                    <?php $pickerModel = 'review.supervisor_id'; $pickerEmpty = 'Choose a teacher'; $pickerId = 'assignSupervisor'; require(base_path('includes/research/supervisor_picker.php')); ?>
                    <template x-for="message in (review.errors.supervisor_id || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

            </div>

            <div x-show="review.decision === 'approve' && review.item && review.item.status === 'pending'">

                <div class="mb-3">
                    <label for="reviewStatus" class="form-label-custom">It becomes</label>
                    <select class="form-select-custom" id="reviewStatus" x-model="review.status">
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                    <template x-for="message in (review.errors.status || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="mb-3">
                    <label for="reviewSupervisor" class="form-label-custom">
                        Supervisor <span class="text-danger" x-show="review.status === 'ongoing'">*</span>
                    </label>
                    <?php $pickerModel = 'review.supervisor_id'; $pickerEmpty = 'No supervisor'; $pickerId = 'reviewSupervisor'; require(base_path('includes/research/supervisor_picker.php')); ?>
                    <template x-for="message in (review.errors.supervisor_id || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

            </div>

            <div class="mb-3" x-show="review.decision !== 'assign'">
                <label for="reviewNote" class="form-label-custom">
                    Note <span x-text="review.decision === 'reject' ? '(the reason, the owner will see it)' : (review.decision === 'correction' ? '(what must be corrected, the owner will see it)' : '(optional)')"></span>
                    <span class="text-danger" x-show="review.decision === 'reject' || review.decision === 'correction'">*</span>
                </label>
                <textarea class="form-control-custom" id="reviewNote" rows="3" maxlength="500" x-model="review.note"></textarea>
                <template x-for="message in (review.errors.note || [])">
                    <div class="text-danger small mt-1" x-text="message"></div>
                </template>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-light" @click="closeReview()">Cancel</button>
                <button type="button" class="btn" :class="{ 'btn-danger': review.decision === 'reject', 'btn-warning': review.decision === 'correction', 'btn-success': review.decision === 'approve', 'btn-primary': review.decision === 'assign' }"
                    :disabled="review.saving" @click="submitReview()"
                    x-text="{ reject: 'Reject for good', correction: 'Send back for correction', assign: 'Assign supervisor', approve: 'Approve research' }[review.decision]"></button>
            </div>

        </div>
    </div>
