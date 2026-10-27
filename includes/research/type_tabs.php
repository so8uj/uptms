<?php
/*
 * The kind of research at the top of a list: All | Project | Thesis | Research Paper.
 * Put it inside a page whose Alpine component has "type" and filtersChanged() (the libraries).
 */
?>

<div class="d-flex flex-wrap gap-2 px-3 pt-3">
    <button type="button" class="filter-pill" :class="type === '' ? 'active' : ''" @click="type = ''; filtersChanged()">All</button>
    <button type="button" class="filter-pill" :class="type === 'Project' ? 'active' : ''" @click="type = 'Project'; filtersChanged()">Project</button>
    <button type="button" class="filter-pill" :class="type === 'Thesis' ? 'active' : ''" @click="type = 'Thesis'; filtersChanged()">Thesis</button>
    <button type="button" class="filter-pill" :class="type === 'Research Paper' ? 'active' : ''" @click="type = 'Research Paper'; filtersChanged()">Research Paper</button>
</div>
