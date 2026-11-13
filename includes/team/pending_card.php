<?php
/*
 * What waits for the thesis committee: new group/team requests and member changes, with Approve and Reject.
 * Put it inside a page whose Alpine component is Sobuj_teamList (team.js).
 */
?>
    <!-- what waits for the admin: on top, with clear buttons -->
    <div class="card border-warning shadow-sm mb-3" x-cloak x-show="pending.length > 0">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-bell-fill text-warning"></i>
                <h6 class="mb-0">Waiting for your decision (<span x-text="pending.length"></span>)</h6>
            </div>

            <template x-for="item in pending" :key="item.id">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top py-2">
                    <div style="min-width: 260px; flex: 1;">
                        <div>
                            <span class="badge-table pending" x-text="pendingKind(item)"></span>
                            <span class="fw-semibold ms-1" x-text="item.name"></span>
                            <span class="text-muted small" x-text="' · ' + item.department_code + ' · ' + item.batch_name"></span>
                        </div>

                        <!-- a new group/team: who is in it -->
                        <div class="small text-muted mt-1" x-show="item.status === 'requested'">
                            Members: <span x-text="members(item)"></span>
                        </div>

                        <!-- a member change: who comes and who goes -->
                        <div class="small mt-1" x-show="item.status !== 'requested' && item.change">
                            <span class="text-muted">Now: <span x-text="members(item)"></span></span><br>
                            <template x-for="student in (item.change ? item.change.added : [])" :key="'a' + student.id">
                                <span class="text-success me-2"><i class="bi bi-plus-circle"></i> <span x-text="student.name"></span></span>
                            </template>
                            <template x-for="student in (item.change ? item.change.removed : [])" :key="'r' + student.id">
                                <span class="text-danger me-2"><i class="bi bi-dash-circle"></i> <span x-text="student.name"></span></span>
                            </template>
                            <span class="text-muted" x-show="item.change && item.change.requested_by_name">
                                (asked by <span x-text="item.change ? item.change.requested_by_name : ''"></span>)
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" @click="approve(item)">
                            <i class="bi bi-check-lg me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" @click="reject(item)">
                            <i class="bi bi-x-lg me-1"></i> Reject
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

