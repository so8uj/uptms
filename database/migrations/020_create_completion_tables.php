<?php

// The request to finish a research (the last step of a project).
//   completion_requests        one request. status says where it is:
//        teacher     a student asked, the supervisor has to decide
//        team        the supervisor approved, every team member has to confirm
//        admin       everybody agreed, the administrator has to decide
//        done        the administrator completed the research
//        rejected    the supervisor or the administrator said no (note = the reason)
//        cancelled   the student who asked took it back
//   completion_confirmations   the team members who confirmed (the student who asked confirms by asking)
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE completion_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            research_id INT UNSIGNED NOT NULL,
            requested_by INT UNSIGNED NOT NULL,
            status VARCHAR(10) NOT NULL DEFAULT 'teacher',
            note VARCHAR(500) NULL,
            decided_by INT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_completion_research (research_id),
            INDEX idx_completion_status (status),
            CONSTRAINT fk_completion_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_completion_requested_by FOREIGN KEY (requested_by) REFERENCES users(id),
            CONSTRAINT fk_completion_decided_by FOREIGN KEY (decided_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE completion_confirmations (
            request_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (request_id, student_id),
            CONSTRAINT fk_confirmation_request FOREIGN KEY (request_id) REFERENCES completion_requests(id),
            CONSTRAINT fk_confirmation_student FOREIGN KEY (student_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
