<?php

/**
 * The project API for the admin pages (admin/research/project.php).
 * The administrator and a teacher with the research management permission may read every project here.
 * It is api/project.php, with the actor taken as the administrator.
 */

define('PROJECT_AS_ADMIN', true);

require __DIR__ . '/project.php';
