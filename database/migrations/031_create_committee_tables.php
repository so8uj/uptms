<?php

// Thesis committees. Only the members of a committee decide on a research topic, a request to finish and a group/team.
//   committees          one row per committee. department_id = NULL means the committee works for the whole university.
//   committee_members   the teachers of a committee (one of them may be the chair).
// A teacher who had the research management permission becomes a member of a first committee, so nobody loses access.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE committees (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            department_id INT UNSIGNED NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_committee_department (department_id),
            CONSTRAINT fk_committee_department FOREIGN KEY (department_id) REFERENCES departments(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE committee_members (
            committee_id INT UNSIGNED NOT NULL,
            teacher_id INT UNSIGNED NOT NULL,
            role ENUM('chair', 'member') NOT NULL DEFAULT 'member',
            PRIMARY KEY (committee_id, teacher_id),
            INDEX idx_committee_teacher (teacher_id),
            CONSTRAINT fk_committee_member_committee FOREIGN KEY (committee_id) REFERENCES committees(id),
            CONSTRAINT fk_committee_member_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $managers = $db->query("
        SELECT u.id FROM users u JOIN teacher_profiles tp ON tp.user_id = u.id
        WHERE u.role = 'teacher' AND u.deleted_at IS NULL AND tp.research_management = 1
    ")->fetchAll(PDO::FETCH_COLUMN);

    if ($managers) {
        $db->exec("INSERT INTO committees (name, department_id) VALUES ('Thesis Committee', NULL)");
        $committeeId = (int) $db->lastInsertId();

        $insert = $db->prepare("INSERT INTO committee_members (committee_id, teacher_id) VALUES (?, ?)");
        foreach ($managers as $teacherId) {
            $insert->execute([$committeeId, (int) $teacherId]);
        }
    }
};
