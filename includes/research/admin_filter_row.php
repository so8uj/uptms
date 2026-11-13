<?php
/*
 * The second row of filters of the admin research lists (Library, Pending, Ongoing):
 * search a student, choose a batch or a year, sort, and clear everything.
 * Put it inside a page whose Alpine component is Sobuj_researchAdminList (research.js).
 */
?>
<div class="d-flex flex-wrap align-items-center gap-2 px-3 pb-3">

    <div class="table-search-box" style="max-width: 230px;">
        <i class="bi bi-person table-search-icon"></i>
        <input type="text" class="table-search-input" placeholder="Student name, ID or email" x-model="student"
            @input.debounce.400ms="filtersChanged()">
    </div>

    <select class="form-select-custom" style="width: auto;" x-model="batchId" @change="filtersChanged()">
        <option value="">All batches</option>
        <template x-for="batch in options.batches" :key="batch.id">
            <option :value="String(batch.id)" x-text="batch.name"></option>
        </template>
    </select>

    <select class="form-select-custom" style="width: auto;" x-model="year" @change="filtersChanged()">
        <option value="">All years</option>
        <template x-for="y in options.years" :key="y">
            <option :value="String(y)" x-text="y"></option>
        </template>
    </select>

    <select class="form-select-custom" style="width: auto;" x-model="sort" @change="filtersChanged()">
        <option value="">Sort: newest first</option>
        <option value="oldest">Sort: oldest first</option>
        <option value="title">Sort: title A to Z</option>
        <option value="title_desc">Sort: title Z to A</option>
        <option value="year_desc">Sort: year, newest first</option>
        <option value="year_asc">Sort: year, oldest first</option>
    </select>

    <button type="button" class="btn btn-link btn-sm" x-cloak x-show="hasFilters()" @click="clearFilters()">
        <i class="bi bi-x-circle me-1"></i> Clear filters
    </button>
</div>
