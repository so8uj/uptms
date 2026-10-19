<?php

// Extra data only students have. One row per student; user_id is the student's row in users.
// student_id stays unique even after the student is deleted, so an ID is never given to two people.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE student_profiles (
            user_id INT UNSIGNED PRIMARY KEY,
            student_id VARCHAR(30) NOT NULL UNIQUE,
            department_id INT UNSIGNED NOT NULL,
            batch_id INT UNSIGNED NOT NULL,
            gender ENUM('Male', 'Female', 'Other') NULL,
            cgpa DECIMAL(3,2) NULL,
            completed_credit_hours SMALLINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_student_user FOREIGN KEY (user_id) REFERENCES users(id),
            CONSTRAINT fk_student_department FOREIGN KEY (department_id) REFERENCES departments(id),
            CONSTRAINT fk_student_batch FOREIGN KEY (batch_id) REFERENCES batches(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
