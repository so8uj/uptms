<?php

// Teams (groups) of students. A team belongs to one department and one batch and has at least 3 members.
// status: open = the team is working (or waiting for a research), closed = its research was completed.
// A student can be in only one open team. A research takes a team through research.team_id,
// and one team works on only one unfinished research at a time.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE teams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            department_id INT UNSIGNED NOT NULL,
            batch_id INT UNSIGNED NOT NULL,
            status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
            created_by INT UNSIGNED NOT NULL,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_teams_department FOREIGN KEY (department_id) REFERENCES departments(id),
            CONSTRAINT fk_teams_batch FOREIGN KEY (batch_id) REFERENCES batches(id),
            CONSTRAINT fk_teams_creator FOREIGN KEY (created_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE team_members (
            team_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (team_id, student_id),
            CONSTRAINT fk_team_members_team FOREIGN KEY (team_id) REFERENCES teams(id),
            CONSTRAINT fk_team_members_user FOREIGN KEY (student_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        ALTER TABLE research
            ADD COLUMN team_id INT UNSIGNED NULL AFTER supervisor_id,
            ADD CONSTRAINT fk_research_team FOREIGN KEY (team_id) REFERENCES teams(id)
    ");
};
