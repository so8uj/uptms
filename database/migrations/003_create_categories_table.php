<?php

// Research categories (for example "Artificial Intelligence").
// deleted_at is the soft delete: NULL = active, a date = deleted. The row itself is never removed.
// There is no UNIQUE index on name on purpose: a deleted category's name must be reusable.
// The Validation "unique" rule keeps active names unique instead.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description VARCHAR(255) NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_categories_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
