<?php

require_once(__DIR__ . '/app/bootstrap.php');

App\Controllers\Auth::logout();

header('Location: ' . url('index.php'));
exit;
