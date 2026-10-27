<?php

// A student cannot change the members of an approved group/team by himself: he asks, the administrator decides.
// team_change_requests = the request (status pending | approved | rejected), team_change_members = the members he wants.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE team_change_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            team_id INT UNSIGNED NOT NULL,
            requested_by INT UNSIGNED NOT NULL,
            status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            review_note VARCHAR(500) NULL,
            reviewed_by INT UNSIGNED NULL,
            reviewed_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_change_team (team_id, status),
            CONSTRAINT fk_change_team FOREIGN KEY (team_id) REFERENCES teams(id),
            CONSTRAINT fk_change_requester FOREIGN KEY (requested_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE team_change_members (
            request_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (request_id, student_id),
            CONSTRAINT fk_change_members_request FOREIGN KEY (request_id) REFERENCES team_change_requests(id),
            CONSTRAINT fk_change_members_user FOREIGN KEY (student_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
