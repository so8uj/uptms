<?php
/*
 * The category select and the tag select of a research search.
 * Put it inside a page whose Alpine component uses Sobuj_searchFilters (research.js).
 */
?>

<select class="form-select-custom" style="width: auto;" x-model="categoryId" @change="refresh()">
    <option value="">All categories</option>
    <template x-for="category in categoryOptions" :key="category.id">
        <option :value="String(category.id)" x-text="category.name"></option>
    </template>
</select>

<div class="dropdown" @click.outside="tagMenuOpen = false">
    <button type="button" class="form-select-custom text-start" style="min-width: 150px;" @click="tagMenuOpen = !tagMenuOpen">
        <span x-text="tagLabel()"></span>
    </button>

    <div class="dropdown-menu p-2" :class="tagMenuOpen ? 'show' : ''" style="min-width: 240px;">
        <input type="text" class="form-control-custom mb-2" placeholder="Find a tag..." x-model="tagSearch">

        <div style="max-height: 220px; overflow-y: auto;">
            <label class="form-check d-flex align-items-center gap-2 py-1 mb-0">
                <input type="checkbox" class="form-check-input mt-0" :checked="tagIds.length === 0" @change="clearTags()">
                <span class="form-check-label">All tags</span>
            </label>

            <template x-for="tag in visibleTags()" :key="tag.id">
                <label class="form-check d-flex align-items-center gap-2 py-1 mb-0">
                    <input type="checkbox" class="form-check-input mt-0" :checked="hasTag(tag.id)" @change="toggleTag(tag.id)">
                    <span class="form-check-label" x-text="tag.name"></span>
                </label>
            </template>

            <div class="small text-muted py-1" x-show="visibleTags().length === 0">No tag found.</div>
        </div>
    </div>
</div>
