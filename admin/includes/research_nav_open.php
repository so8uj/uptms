<?php
// The left menu of the admin research pages.
$file = current_file();

$sectionNav = [
    ['Research Library',      'admin/research/index.php',                 in_array($file, ['index.php', 'view.php', 'project.php'], true), null],
    ['Add Thesis / Research', 'admin/research/manage.php?action=create',  $file === 'manage.php',                                           null],
    ['Pending Submissions',   'admin/research/pending.php',               $file === 'pending.php',                                          'new_requests'],
    ['Ongoing Research',      'admin/research/ongoing.php',               $file === 'ongoing.php',                                          'in_progress'],
    ['Completion Requests',   'admin/research/completion.php',            $file === 'completion.php',                                       'completion'],
    ['Research Categories',   'admin/research/categories.php',            $file === 'categories.php',                                       null],
    ['Research Tags',         'admin/research/tags.php',                  $file === 'tags.php',                                             null],
];

require(base_path('admin/includes/section_nav_open.php'));
