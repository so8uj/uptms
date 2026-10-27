<?php
/*
 * The member picker of a group/team: the chosen students and the students who can join.
 * Put it inside a component that uses Sobuj_teamPicker (team.js) and has pick(), searchChanged(), minSize and errors.
 */
?>

<label class="form-label-custom">
    Members <span class="text-danger">*</span>
    <span class="text-muted fw-normal"><span x-text="minSize"></span> to <span x-text="maxSize"></span> students, selected: <strong x-text="chosen.length"></strong></span>
</label>

<div class="d-flex flex-wrap gap-2 mb-2">
    <template x-for="student in chosen" :key="student.id">
        <span class="badge-table pending" x-text="label(student)"></span>
    </template>
    <small class="text-muted" x-show="chosen.length === 0">No student chosen yet.</small>
</div>

<div class="small text-danger mb-1" x-show="sizeProblem() !== ''" x-text="sizeProblem()"></div>

<input type="text" class="form-control-custom mb-2" autocomplete="off" placeholder="Search by name or student ID..."
    x-model="search" @input="searchChanged()">

<div class="list-group" style="max-height: 280px; overflow-y: auto;">
    <template x-for="student in candidates" :key="student.id">
        <label class="list-group-item d-flex align-items-center gap-2 mb-0"
            :class="student.busy ? 'text-muted bg-light' : 'list-group-item-action'" :style="student.busy ? '' : 'cursor: pointer;'">
            <input type="checkbox" class="form-check-input mt-0" :checked="isChosen(student)" :disabled="!!student.busy || locked(student)" @change="pick(student)">
            <span x-text="label(student)"></span>
            <small class="ms-auto" x-show="student.busy" x-text="student.busy"></small>
            <small class="ms-auto text-muted" x-show="locked(student)">You (always a member)</small>
        </label>
    </template>
    <div class="list-group-item text-muted small" x-show="candidates.length === 0">
        No student found in this batch. Students who are in a group/team or working on a research are shown greyed out, with the reason.
    </div>
</div>

<template x-for="message in (errors.students || [])">
    <div class="text-danger small mt-1" x-text="message"></div>
</template>
