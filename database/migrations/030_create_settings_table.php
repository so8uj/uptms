<?php

// The system settings the admin can change on the Settings page: one row per setting (name = value).
// The first values are the ones the project used before this table existed.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE settings (
            name VARCHAR(60) NOT NULL PRIMARY KEY,
            value VARCHAR(500) NOT NULL DEFAULT '',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $insert = $db->prepare("INSERT INTO settings (name, value) VALUES (?, ?)");

    foreach ([
        ['institution_name', 'World University of Bangladesh'],
        ['department_name', 'Department of CSE'],
        ['support_email', ''],
        ['team_min_size', '2'],
        ['team_max_size', '3'],
        ['login_max_attempts', '5'],
        ['login_lock_minutes', '5'],
        ['maintenance_mode', '0'],
        ['maintenance_message', 'The system is under maintenance. Please try again later.'],
    ] as $row) {
        $insert->execute($row);
    }
};
