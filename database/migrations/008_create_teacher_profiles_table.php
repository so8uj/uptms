<?php

// Extra data only teachers have. One row per teacher; user_id is the teacher's row in users.
// can_add_research = the permission that lets this teacher add research (the admin switches it on).
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE teacher_profiles (
            user_id INT UNSIGNED PRIMARY KEY,
            employee_id VARCHAR(30) NOT NULL UNIQUE,
            department_id INT UNSIGNED NOT NULL,
            designation VARCHAR(60) NOT NULL,
            specialization VARCHAR(150) NULL,
            research_area VARCHAR(150) NULL,
            can_add_research TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_teacher_user FOREIGN KEY (user_id) REFERENCES users(id),
            CONSTRAINT fk_teacher_department FOREIGN KEY (department_id) REFERENCES departments(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
