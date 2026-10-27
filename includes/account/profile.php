<?php
/*
 * The Profile page of every role (admin, teacher, student).
 * Put it inside a page whose Alpine component is Sobuj_accountPage (account.js).
 * One card, three views: the details, edit name and phone, change password.
 */
?>

<div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
    x-text="toast.text"></div>

<div x-cloak x-show="loading" class="text-center text-muted py-5">Loading...</div>

<div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

<template x-if="account">
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card border-light shadow-sm">
                <div class="card-body">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar-edit">
                            <img x-show="avatar" :src="avatar" alt="" class="navbar-profile-img" style="width: 72px; height: 72px;">
                            <div class="table-user-avatar" style="width: 72px; height: 72px; font-size: 1.5rem;" x-show="!avatar" x-text="Sobuj_initials(account.name)"></div>
                            <button type="button" class="avatar-edit-btn" title="Change picture" aria-label="Change picture"
                                :disabled="photoSaving" @click="$refs.photoInput.click()">
                                <i class="bi" :class="photoSaving ? 'bi-hourglass-split' : 'bi-pencil-fill'"></i>
                            </button>
                            <input type="file" x-ref="photoInput" hidden accept="image/jpeg,image/png,image/webp" @change="changePhoto($event)">
                        </div>
                        <div>
                            <h5 class="mb-1" x-text="account.name"></h5>
                            <div class="text-muted small mb-1" x-show="account.role === 'teacher' && account.designation"
                                x-text="(account.designation || '') + (account.department_name ? ' · ' + account.department_name : '')"></div>
                            <div class="text-muted small mb-1" x-show="account.role === 'student' && account.student_id"
                                x-text="(account.student_id || '') + (account.batch_name ? ' · Batch ' + account.batch_name : '')"></div>
                            <span class="badge-table" :class="account.status == 1 ? 'success' : 'failed'"
                                x-text="account.status == 1 ? 'Active' : 'Inactive'"></span>
                        </div>
                    </div>

                    <div x-show="view === 'show'">
                        <div class="mb-3">
                            <div class="small text-muted">Email</div>
                            <div class="fw-semibold" x-text="account.email"></div>
                        </div>
                        <div class="mb-4">
                            <div class="small text-muted">Phone</div>
                            <div class="fw-semibold" x-text="account.phone || '-'"></div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" @click="openEdit()">
                                <i class="bi bi-pencil me-1"></i> Update Profile
                            </button>
                            <button type="button" class="btn btn-outline-primary" @click="openPassword()">
                                <i class="bi bi-shield-lock me-1"></i> Update Password
                            </button>
                        </div>
                    </div>

                    <form x-show="view === 'edit'" @submit.prevent="save()" novalidate>
                        <div class="mb-3">
                            <label for="accName" class="form-label-custom">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-custom" id="accName" maxlength="100" x-model="form.name">
                            <template x-for="message in (form.errors.name || [])">
                                <div class="text-danger small mt-1" x-text="message"></div>
                            </template>
                        </div>

                        <div class="mb-3">
                            <label for="accEmail" class="form-label-custom"><i class="bi bi-lock-fill me-1"></i>Email</label>
                            <input type="email" class="form-control-custom" id="accEmail" x-model="account.email" readonly disabled>
                            <div class="text-muted small mt-1">The email cannot be changed here. Please ask the administrator.</div>
                        </div>

                        <div class="mb-4">
                            <label for="accPhone" class="form-label-custom">Phone</label>
                            <input type="text" class="form-control-custom" id="accPhone" maxlength="20" x-model="form.phone">
                            <template x-for="message in (form.errors.phone || [])">
                                <div class="text-danger small mt-1" x-text="message"></div>
                            </template>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="form.saving">Update Profile</button>
                            <button type="button" class="btn btn-light" @click="view = 'show'">Cancel</button>
                        </div>
                    </form>

                    <form x-show="view === 'password'" @submit.prevent="changePassword()" novalidate>
                        <div class="mb-3">
                            <label for="accOld" class="form-label-custom">Current password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control-custom" id="accOld" autocomplete="current-password" x-model="password.old_password">
                            <template x-for="message in (password.errors.old_password || [])">
                                <div class="text-danger small mt-1" x-text="message"></div>
                            </template>
                        </div>

                        <div class="mb-3">
                            <label for="accNew" class="form-label-custom">New password (at least 8 characters) <span class="text-danger">*</span></label>
                            <input type="password" class="form-control-custom" id="accNew" autocomplete="new-password" x-model="password.new_password">
                            <template x-for="message in (password.errors.new_password || [])">
                                <div class="text-danger small mt-1" x-text="message"></div>
                            </template>
                        </div>

                        <div class="mb-4">
                            <label for="accAgain" class="form-label-custom">Confirm new password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control-custom" id="accAgain" autocomplete="new-password" x-model="password.new_password_again">
                            <template x-for="message in (password.errors.new_password_again || [])">
                                <div class="text-danger small mt-1" x-text="message"></div>
                            </template>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="password.saving">Update Password</button>
                            <button type="button" class="btn btn-light" @click="view = 'show'">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</template>
