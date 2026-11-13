<?php
// The left menu of the admin university pages. The folder of the page tells which item is active.
$folder = current_folder();

// Teachers and students come first. Then the structure in the order of its dependency:
// a batch needs a department and a session. The groups/teams are last, they need students.
$sectionNav = [
    ['Teachers',          'admin/university/teachers/index.php',    $folder === 'teachers',    null],
    ['Students',          'admin/university/students/index.php',    $folder === 'students',    null],
    ['Departments',       'admin/university/departments/index.php', $folder === 'departments', null],
    ['Academic Sessions', 'admin/university/sessions/index.php',    $folder === 'sessions',    null],
    ['Batches',           'admin/university/batches/index.php',     $folder === 'batches',     null],
    ['Groups/Teams',      'admin/university/teams/index.php',       $folder === 'teams',       'team_requests'],
    ['Thesis Committees', 'admin/university/committees/index.php',  $folder === 'committees',  null],
];

require(base_path('admin/includes/section_nav_open.php'));
