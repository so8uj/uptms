<?php

// The students who work on a research (optional). student_id is the student's row in users.
// A student who asks for a topic is added here automatically.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE research_student (
            research_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (research_id, student_id),
            CONSTRAINT fk_research_student_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_research_student_user FOREIGN KEY (student_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
