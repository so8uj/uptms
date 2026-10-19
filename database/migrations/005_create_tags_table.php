<?php

// Research tags (for example "machine learning"). Same shape and same rules as categories:
// deleted_at is the soft delete, and there is no UNIQUE index on name so a deleted tag's name can be reused.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE tags (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description VARCHAR(255) NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tags_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
