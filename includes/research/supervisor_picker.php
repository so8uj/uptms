<?php
/*
 * A supervisor picker with search: click the box, type a name or an employee ID, click a teacher.
 * Put it inside a page whose Alpine component has <?= $pickerList ?? 'options.teachers' ?> (research.js).
 *
 * The page sets before requiring this file:
 *   $pickerModel  the Alpine value it fills, for example 'review.supervisor_id'
 *   $pickerEmpty  the words for "nobody", for example 'No supervisor' (or 'Choose a teacher')
 *   $pickerId     the id of the box (for the label)
 *   $pickerList   (optional) the Alpine list of teachers to search, default options.teachers
 */
?>
<div x-data="{ open: false, q: '' }" x-init="$watch('open', function (shown) { if (shown) { setTimeout(function () { $refs.search.focus(); }, 30); } })" @click.outside="open = false">

    <button type="button" class="form-select-custom text-start w-100" id="<?= $pickerId ?>"
        @click="open = !open; q = ''">
        <span x-text="Sobuj_teacherLabel(<?= $pickerList ?? 'options.teachers' ?>, <?= $pickerModel ?>, '<?= $pickerEmpty ?>')"></span>
    </button>

    <div x-cloak x-show="open" class="border rounded-3 bg-white mt-1">
        <div class="p-2 border-bottom">
            <input type="text" class="form-control-custom" x-ref="search" x-model="q" autocomplete="off"
                placeholder="Search name or employee ID..."
                @keydown.escape.stop="open = false"
                @keydown.enter.prevent="const first = Sobuj_teacherMatches(<?= $pickerList ?? 'options.teachers' ?>, q)[0]; if (first) { <?= $pickerModel ?> = String(first.id); open = false; }">
        </div>

        <div style="max-height: 220px; overflow-y: auto;">
            <div class="px-3 py-2 small text-muted" style="cursor: pointer;" @click="<?= $pickerModel ?> = ''; open = false"
                x-text="'<?= $pickerEmpty ?>'"></div>

            <template x-for="teacher in Sobuj_teacherMatches(<?= $pickerList ?? 'options.teachers' ?>, q)" :key="teacher.id">
                <div class="px-3 py-2" style="cursor: pointer;"
                    :class="String(teacher.id) === String(<?= $pickerModel ?>) ? 'fw-semibold bg-light' : ''"
                    @click="<?= $pickerModel ?> = String(teacher.id); open = false"
                    @mouseenter="$el.classList.add('bg-light')" @mouseleave="if (String(teacher.id) !== String(<?= $pickerModel ?>)) $el.classList.remove('bg-light')">
                    <span x-text="teacher.name"></span>
                    <span class="text-muted small" x-show="teacher.employee_id" x-text="'(' + teacher.employee_id + ')'"></span>
                </div>
            </template>

            <div class="px-3 py-2 small text-muted" x-show="Sobuj_teacherMatches(<?= $pickerList ?? 'options.teachers' ?>, q).length === 0">No teacher found.</div>
        </div>
    </div>
</div>
