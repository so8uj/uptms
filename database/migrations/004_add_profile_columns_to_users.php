<?php

// Columns every kind of user needs (admin, teacher, student).
// photo      = relative path of the uploaded picture, for example photos/2026/ab12cd.jpg. NULL = no photo,
//              and the pages then show the user's initials instead.
// deleted_at = soft delete. NULL = active, a date = deleted. The row is never removed.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE users
            ADD phone VARCHAR(20) NULL AFTER email,
            ADD photo VARCHAR(255) NULL AFTER phone,
            ADD deleted_at TIMESTAMP NULL DEFAULT NULL AFTER locked_until
    ");
};
