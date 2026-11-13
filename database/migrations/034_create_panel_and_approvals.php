<?php

// The signing panel of a research and the signatures.
//   research_panel   the 2 or 3 teachers (members of a thesis committee) who sign the topic and the finish of one research
//   approvals        one row per signature: what is signed (a topic or a request to finish), who, and the answer
// 2 signatures decide (the number is a setting). The supervisor may be one of the panel teachers.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE research_panel (
            research_id INT UNSIGNED NOT NULL,
            teacher_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (research_id, teacher_id),
            INDEX idx_panel_teacher (teacher_id),
            CONSTRAINT fk_panel_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_panel_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE approvals (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            subject ENUM('topic', 'completion') NOT NULL,
            subject_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            decision ENUM('approve', 'correction', 'reject') NOT NULL,
            note VARCHAR(500) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_approval (subject, subject_id, user_id),
            CONSTRAINT fk_approval_user FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("INSERT INTO settings (name, value) VALUES ('panel_signatures_needed', '2')");
};
