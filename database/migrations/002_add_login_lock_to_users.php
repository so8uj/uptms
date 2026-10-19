<?php

// Adds the two columns the login rate limit needs.
// failed_attempts = wrong logins in a row, locked_until = until when this account cannot log in.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE users
            ADD failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER status,
            ADD locked_until TIMESTAMP NULL DEFAULT NULL AFTER failed_attempts
    ");
};
