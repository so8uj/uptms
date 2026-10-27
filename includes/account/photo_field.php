<?php
/*
 * The picture on the admin Add/Edit forms (admins, teachers, students).
 * Put it inside a form whose Alpine component is Sobuj_userForm (users.js).
 * The pencil opens the file chooser (the input itself is hidden). The chosen picture is shown at once and saved with the form.
 */
?>
<div class="d-flex align-items-center gap-4 mb-4">

    <div class="avatar-edit avatar-edit-lg">
        <template x-if="shownPhoto">
            <img class="table-user-avatar" style="width: 110px; height: 110px;" :src="shownPhoto" alt="Photo">
        </template>
        <template x-if="!shownPhoto">
            <div class="table-user-avatar" style="width: 110px; height: 110px; font-size: 2rem;" x-text="Sobuj_initials(form.name)"></div>
        </template>

        <button type="button" class="avatar-edit-btn" title="Choose a picture" aria-label="Choose a picture" @click="$refs.photoInput.click()">
            <i class="bi bi-pencil-fill"></i>
        </button>
        <input type="file" id="photo" x-ref="photoInput" hidden accept="image/jpeg,image/png,image/webp" @change="photoChosen($event)">
    </div>

    <div>
        <small class="text-muted d-block">Click the pencil to choose a picture. JPG, PNG or WebP, up to 2 MB. It is saved with the form.</small>
        <template x-for="message in (errors.photo || [])">
            <div class="text-danger small mt-1" x-text="message"></div>
        </template>
        <button type="button" class="btn btn-link btn-sm p-0 mt-1" x-cloak x-show="photoFile"
            @click="clearPhoto()">Remove the selected photo</button>
    </div>

</div>

