<?php

require_once(__DIR__ . '/../app/bootstrap.php');

// Feedback: the list of my projects first, then one project (see includes/project/page.php).
$role = 'teacher';
$pageHeader = "Feedback";
$file = 'feedback.php';
$tab = 'comments';

require(base_path('includes/project/page.php'));
