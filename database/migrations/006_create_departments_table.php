<?php

// Departments (for example "Computer Science & Engineering", code "CSE").
// Soft delete with deleted_at, and no UNIQUE index on name/code: Validation keeps the active ones unique.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE departments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) NOT NULL,
            description VARCHAR(255) NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_departments_name (name),
            INDEX idx_departments_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
