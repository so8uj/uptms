<?php

// The work on an ongoing research (the "project"): milestones with tasks, comments and the teacher's evaluations.
//   milestones   a step of the research, made by the supervisor or a student. Soft deleted.
//   tasks        a small job inside a milestone. is_done tells if it is finished and who finished it. Soft deleted.
//   research_comments   the talk between the supervisor and the team. Soft deleted (a person can remove their own).
//   evaluations  the supervisor's progress report: a rating from 1 to 5 and remarks. Never changed after saving.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE milestones (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            research_id INT UNSIGNED NOT NULL,
            title VARCHAR(150) NOT NULL,
            description VARCHAR(500) NULL,
            due_date DATE NULL,
            created_by INT UNSIGNED NOT NULL,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_milestones_research (research_id),
            CONSTRAINT fk_milestones_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_milestones_user FOREIGN KEY (created_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE tasks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            milestone_id INT UNSIGNED NOT NULL,
            title VARCHAR(200) NOT NULL,
            is_done TINYINT(1) NOT NULL DEFAULT 0,
            completed_by INT UNSIGNED NULL,
            completed_at TIMESTAMP NULL DEFAULT NULL,
            created_by INT UNSIGNED NOT NULL,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tasks_milestone (milestone_id),
            CONSTRAINT fk_tasks_milestone FOREIGN KEY (milestone_id) REFERENCES milestones(id),
            CONSTRAINT fk_tasks_created_by FOREIGN KEY (created_by) REFERENCES users(id),
            CONSTRAINT fk_tasks_completed_by FOREIGN KEY (completed_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE research_comments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            research_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            body VARCHAR(1000) NOT NULL,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_comments_research (research_id),
            CONSTRAINT fk_comments_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE evaluations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            research_id INT UNSIGNED NOT NULL,
            teacher_id INT UNSIGNED NOT NULL,
            rating TINYINT UNSIGNED NOT NULL,
            remarks VARCHAR(1000) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_evaluations_research (research_id),
            CONSTRAINT fk_evaluations_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_evaluations_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
