<?php

require_once(__DIR__ . '/../app/bootstrap.php');

// Milestones: the list of my projects first, then one project (see includes/project/page.php).
$role = 'student';
$pageHeader = "Milestones";
$file = 'milestones.php';
$tab = 'milestones';

require(base_path('includes/project/page.php'));
