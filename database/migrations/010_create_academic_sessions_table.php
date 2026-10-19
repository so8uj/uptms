<?php

// Academic sessions: the terms the university runs (for example "Spring 2026" or "2026").
// term_type says how that session is counted: yearly, semester or trimester.
// is_current marks the session that is running now; there is one current session per term_type.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE academic_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            term_type ENUM('yearly', 'semester', 'trimester') NOT NULL,
            year SMALLINT UNSIGNED NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            is_current TINYINT(1) NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 1,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_sessions_name (name),
            INDEX idx_sessions_type (term_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
