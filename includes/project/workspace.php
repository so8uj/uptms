<?php
/*
 * The project page: details, milestones with tasks, evaluations and comments.
 * Put it inside a page whose Alpine component is Sobuj_projectWorkspace (project.js).
 * The page sets $backUrl (the list of projects).
 */
?>

<div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
    x-text="toast.text"></div>

<div x-cloak x-show="loading" class="text-center text-muted py-5">Loading...</div>

<div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

<template x-if="data">
    <div>

        <div class="card border-light shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted" x-text="data.research.code + ' · ' + data.research.type + ' · ' + data.research.category_name + ' · ' + data.research.year"></div>
                        <h4 class="mt-1 mb-2" x-text="data.research.title"></h4>
                    </div>
                    <span class="badge-table" :class="Sobuj_statusBadge(data.research.status)" x-text="Sobuj_statusText(data.research.status)"></span>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-4">
                        <div class="small text-muted">Supervisor</div>
                        <div class="fw-semibold" x-text="data.research.supervisor_name || '-'"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="small text-muted">Group/Team</div>
                        <div class="fw-semibold" x-text="data.research.team_name || 'No group/team'"></div>
                        <div class="small text-muted" x-show="data.research.batch_name" x-text="'Batch ' + data.research.batch_name"></div>
                        <div class="small text-muted" x-text="Sobuj_authors(data.research)"></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="small text-muted">Progress (<span x-text="data.progress.done + ' of ' + data.progress.tasks"></span> tasks done)</div>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" :style="'width: ' + data.progress.percent + '%;'"></div>
                        </div>
                        <div class="small mt-1" x-text="data.progress.percent + '%'"></div>
                    </div>
                </div>

                <div class="alert alert-secondary py-2 small mt-3 mb-0" x-show="data.research.status === 'completed'">
                    This research is completed. You can read everything, but nothing can be changed.
                </div>
            </div>
        </div>

        <div class="card border-light shadow-sm mb-4" x-show="data.research.status === 'ongoing' || data.completion.request">
            <div class="card-header">
                <h5 class="card-title">Finishing the research</h5>
                <span class="badge-table pending" x-show="isRequestAlive()"
                    x-text="isRequestAlive() ? { teacher: 'Waiting for the supervisor', team: 'Waiting for the team', admin: 'Waiting for the administrator' }[data.completion.request.status] : ''"></span>
            </div>
            <div class="card-body">

                <div class="alert alert-danger py-2 small" x-show="data.completion.request && data.completion.request.status === 'rejected'">
                    The last request was rejected: <strong x-text="data.completion.request ? data.completion.request.note : ''"></strong>
                </div>

                <div class="alert alert-success py-2 small mb-0" x-show="data.research.status === 'completed'">This research is completed.</div>

                <div x-show="data.research.status === 'ongoing' && !isRequestAlive()">
                    <p class="text-muted small mb-2" x-show="!data.completion.can_request">No request to finish this research is open. A student of the team can ask when the work is done.</p>
                    <div x-show="data.completion.can_request">
                        <p class="text-muted small">When the work is done, ask your supervisor to finish the research. The supervisor approves, then every team member confirms, then the administrator completes it.</p>
                        <button type="button" class="btn btn-primary btn-sm" @click="requestCompletion()">Request to finish</button>
                    </div>
                </div>

                <div x-show="isRequestAlive()">
                    <template x-for="(step, index) in completionSteps()" :key="index">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi" :class="step.done ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'"></i>
                            <span :class="step.done ? '' : 'text-muted'" x-text="step.text"></span>
                        </div>
                    </template>

                    <div class="small text-muted ms-4 mb-3" x-show="data.completion.request && data.completion.request.status !== 'teacher'">
                        <template x-for="member in (data.completion.request ? data.completion.request.members : [])" :key="member.id">
                            <span class="me-3"><i class="bi" :class="member.confirmed ? 'bi-check2 text-success' : 'bi-hourglass-split'"></i> <span x-text="member.name"></span></span>
                        </template>
                    </div>

                    <div x-show="data.completion.can_confirm || data.completion.can_cancel">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm" x-show="data.completion.can_confirm" @click="confirmCompletion()">I confirm the work is finished</button>
                            <button type="button" class="btn btn-light btn-sm" x-show="data.completion.can_cancel" @click="cancelCompletion()">Cancel the request</button>
                        </div>
                    </div>

                    <div x-show="data.completion.can_decide" class="border rounded p-3">
                        <label for="completionNote" class="form-label-custom">Note (needed when you reject)</label>
                        <textarea class="form-control-custom mb-1" id="completionNote" rows="2" maxlength="500" x-model="completionNote"></textarea>
                        <template x-for="message in (completionErrors.note || [])">
                            <div class="text-danger small mb-1" x-text="message"></div>
                        </template>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-primary btn-sm" @click="decideCompletion('approve')">Approve</button>
                            <button type="button" class="btn btn-danger btn-sm" @click="decideCompletion('reject')">Reject</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="row g-4">

            <div class="col-12 col-xl-8">
                <div class="card border-light shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title">Milestones</h5>
                        <button type="button" class="btn btn-sm btn-primary" x-show="data.can_work && !milestoneForm.open" @click="openMilestoneForm()">
                            <i class="bi bi-plus-lg"></i> Add milestone
                        </button>
                    </div>
                    <div class="card-body">

                        <div class="border rounded p-3 mb-4" x-show="milestoneForm.open">
                            <div class="mb-3">
                                <label for="milestoneTitle" class="form-label-custom">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-custom" id="milestoneTitle" maxlength="150" x-model="milestoneForm.title"
                                    placeholder="For example Literature review">
                                <template x-for="message in (milestoneForm.errors.title || [])">
                                    <div class="text-danger small mt-1" x-text="message"></div>
                                </template>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <label for="milestoneDescription" class="form-label-custom">Description</label>
                                    <input type="text" class="form-control-custom" id="milestoneDescription" maxlength="500" x-model="milestoneForm.description">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="milestoneDue" class="form-label-custom">Due date</label>
                                    <input type="date" class="form-control-custom" id="milestoneDue" x-model="milestoneForm.due_date">
                                    <template x-for="message in (milestoneForm.errors.due_date || [])">
                                        <div class="text-danger small mt-1" x-text="message"></div>
                                    </template>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" @click="addMilestone()">Save milestone</button>
                                <button type="button" class="btn btn-light btn-sm" @click="milestoneForm.open = false">Cancel</button>
                            </div>
                        </div>

                        <div class="text-muted small" x-show="data.milestones.length === 0">No milestone yet.</div>

                        <template x-for="milestone in data.milestones" :key="milestone.id">
                            <div class="border rounded p-3 mb-3">

                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold" x-text="milestone.title"></div>
                                        <div class="small text-muted" x-show="milestone.description" x-text="milestone.description"></div>
                                        <div class="small text-muted mt-1">
                                            <span x-text="'By ' + milestone.creator_name"></span>
                                            <span x-show="milestone.due_date" x-text="' · Due ' + Sobuj_formatDate(milestone.due_date)"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted" x-text="milestonePercent(milestone) + '%'"></span>
                                        <button type="button" class="table-btn-action delete" title="Delete milestone" @click="deleteMilestone(milestone)"
                                            x-show="canDelete(milestone.created_by)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="progress my-2" style="height: 5px;">
                                    <div class="progress-bar bg-success" :style="'width: ' + milestonePercent(milestone) + '%;'"></div>
                                </div>

                                <div class="small text-muted" x-show="milestone.tasks.length === 0">No task yet.</div>

                                <template x-for="task in milestone.tasks" :key="task.id">
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <label class="form-check d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                                            <input type="checkbox" class="form-check-input mt-0" :checked="task.is_done === 1" :disabled="!data.can_work"
                                                @change="toggleTask(task)">
                                            <span :class="task.is_done === 1 ? 'text-decoration-line-through text-muted' : ''" x-text="task.title"></span>
                                        </label>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small text-muted" x-show="task.is_done === 1" x-text="'Done by ' + task.completed_by_name"></span>
                                            <button type="button" class="btn btn-sm btn-light" title="Delete task" @click="deleteTask(task)"
                                                x-show="canDelete(task.created_by)">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="data.can_work">
                                    <div class="d-flex gap-2 mt-2">
                                        <input type="text" class="form-control-custom" maxlength="200" placeholder="Add a task..."
                                            x-model="taskTitles[milestone.id]" @keydown.enter.prevent="addTask(milestone)">
                                        <button type="button" class="btn btn-sm btn-primary" @click="addTask(milestone)">Add</button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-light" @click="feedbackOpen[milestone.id] = !feedbackOpen[milestone.id]">
                                        <i class="bi bi-chat-left-text"></i> Feedback (<span x-text="feedbackFor(milestone).length"></span>)
                                    </button>

                                    <div class="mt-2" x-show="feedbackOpen[milestone.id]">
                                        <div class="text-muted small" x-show="feedbackFor(milestone).length === 0">No feedback on this milestone yet.</div>

                                        <template x-for="comment in feedbackFor(milestone)" :key="comment.id">
                                            <div class="bg-light rounded p-2 mb-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="small">
                                                        <span class="fw-semibold" x-text="comment.user_name"></span>
                                                        <span class="badge-table pending" x-text="comment.user_role === 'teacher' ? 'Supervisor' : 'Student'"></span>
                                                        <span class="text-muted" x-text="' · ' + comment.created_at.substring(0, 16)"></span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light" title="Delete" @click="deleteComment(comment)"
                                                        x-show="data.can_work && comment.user_id === data.me.id">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                                <div class="small" style="white-space: pre-line;" x-text="comment.body"></div>
                                            </div>
                                        </template>

                                        <div x-show="data.can_work">
                                            <div class="d-flex gap-2">
                                                <input type="text" class="form-control-custom" maxlength="1000" placeholder="Write your feedback on this milestone..."
                                                    x-model="feedbackText[milestone.id]" @keydown.enter.prevent="addFeedback(milestone)">
                                                <button type="button" class="btn btn-sm btn-primary" @click="addFeedback(milestone)">Send</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>

                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">

                <div class="card border-light shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Evaluations</h5>
                    </div>
                    <div class="card-body">

                        <div class="border rounded p-3 mb-3" x-show="data.can_evaluate">
                            <label for="evalRating" class="form-label-custom">Rating <span class="text-danger">*</span></label>
                            <select class="form-select-custom mb-1" id="evalRating" x-model="evaluationForm.rating">
                                <option value="">Choose a rating</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                            <template x-for="message in (evaluationForm.errors.rating || [])">
                                <div class="text-danger small mb-1" x-text="message"></div>
                            </template>

                            <label for="evalRemarks" class="form-label-custom mt-2">Report on the progress <span class="text-danger">*</span></label>
                            <textarea class="form-control-custom mb-1" id="evalRemarks" rows="3" maxlength="1000" x-model="evaluationForm.remarks"></textarea>
                            <template x-for="message in (evaluationForm.errors.remarks || [])">
                                <div class="text-danger small mb-1" x-text="message"></div>
                            </template>

                            <button type="button" class="btn btn-primary btn-sm mt-2" @click="addEvaluation()">Save evaluation</button>
                        </div>

                        <div class="text-muted small" x-show="data.evaluations.length === 0">No evaluation yet.</div>

                        <template x-for="evaluation in data.evaluations" :key="evaluation.id">
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold" x-text="evaluation.rating + ' of 5 · ' + Sobuj_ratingText(evaluation.rating)"></span>
                                    <span class="small text-muted" x-text="Sobuj_formatDate(evaluation.created_at)"></span>
                                </div>
                                <div class="small" style="white-space: pre-line;" x-text="evaluation.remarks"></div>
                                <div class="small text-muted" x-text="evaluation.teacher_name"></div>
                            </div>
                        </template>

                    </div>
                </div>

                <div class="card border-light shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title">Comments</h5>
                    </div>
                    <div class="card-body">

                        <div class="mb-3" x-show="data.can_work">
                            <textarea class="form-control-custom mb-2" rows="2" maxlength="1000" placeholder="Write a comment..." x-model="commentBody"></textarea>
                            <button type="button" class="btn btn-primary btn-sm" @click="addComment()">Post comment</button>
                        </div>

                        <div class="text-muted small" x-show="generalComments().length === 0">No comment yet.</div>

                        <template x-for="comment in generalComments()" :key="comment.id">
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="fw-semibold" x-text="comment.user_name"></span>
                                        <span class="badge-table pending" x-text="comment.user_role === 'teacher' ? 'Supervisor' : 'Student'"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light" title="Delete comment" @click="deleteComment(comment)"
                                        x-show="data.can_work && comment.user_id === data.me.id">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="small" style="white-space: pre-line;" x-text="comment.body"></div>
                                <div class="small text-muted" x-text="comment.created_at.substring(0, 16)"></div>
                            </div>
                        </template>

                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<a href="<?= $backUrl ?>" class="btn btn-light mt-3"><i class="bi bi-arrow-left"></i> Back to My Research</a>
