<?php

// Batches (for example "65B", session "2025-2026"). Every batch belongs to one department.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE batches (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            code VARCHAR(30) NOT NULL,
            session VARCHAR(20) NOT NULL,
            department_id INT UNSIGNED NOT NULL,
            description VARCHAR(255) NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_batches_name (name),
            CONSTRAINT fk_batches_department FOREIGN KEY (department_id) REFERENCES departments(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
