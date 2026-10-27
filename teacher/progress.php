<?php

require_once(__DIR__ . '/../app/bootstrap.php');

// Progress Updates: the list of my projects first, then one project (see includes/project/page.php).
$role = 'teacher';
$pageHeader = "Progress Updates";
$file = 'progress.php';
$tab = 'activity';

require(base_path('includes/project/page.php'));
