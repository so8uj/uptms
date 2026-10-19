<?php

// The research / thesis catalog. Only the details the thesis committee wants: no files, no methodology.
//
// status:
//   requested  a student asked for a topic (waiting for the admin)
//   pending    a teacher added a research (waiting for the admin)
//   ongoing    approved and running
//   completed  finished, shown in the library
//   rejected   the admin said no (review_note says why)
// target_status = what the person who submitted it wants it to become when approved (ongoing or completed).
// code is made after the row is saved: RS-<year>-<id padded to 4>, for example RS-2026-0007.
// supervisor_id and the students in research_student are optional.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE research (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(20) NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            abstract TEXT NOT NULL,
            type ENUM('Thesis', 'Research Paper', 'Project') NOT NULL DEFAULT 'Thesis',
            category_id INT UNSIGNED NOT NULL,
            year SMALLINT UNSIGNED NOT NULL,
            status ENUM('requested', 'pending', 'ongoing', 'completed', 'rejected') NOT NULL,
            target_status ENUM('ongoing', 'completed') NOT NULL DEFAULT 'ongoing',
            supervisor_id INT UNSIGNED NULL,
            created_by INT UNSIGNED NOT NULL,
            review_note VARCHAR(500) NULL,
            reviewed_at TIMESTAMP NULL DEFAULT NULL,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_research_status (status),
            INDEX idx_research_title (title),
            CONSTRAINT fk_research_category FOREIGN KEY (category_id) REFERENCES categories(id),
            CONSTRAINT fk_research_supervisor FOREIGN KEY (supervisor_id) REFERENCES users(id),
            CONSTRAINT fk_research_creator FOREIGN KEY (created_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
