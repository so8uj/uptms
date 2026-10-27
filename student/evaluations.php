<?php

require_once(__DIR__ . '/../app/bootstrap.php');

// Evaluations: the list of my projects first, then one project (see includes/project/page.php).
$role = 'student';
$pageHeader = "Evaluations";
$file = 'evaluations.php';
$tab = 'evaluations';

require(base_path('includes/project/page.php'));
