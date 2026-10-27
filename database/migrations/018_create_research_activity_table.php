<?php

// The history of a research: every action, who did it and when (requested, approved, supervisor assigned ...).
//   user_id   the person who did it. NULL only for old history that was filled in from earlier data (shown as "Administrator").
//   action    requested, submitted, added, updated, resubmitted, approved, rejected, supervisor_assigned,
//             member_added, member_removed, completed, withdrawn, deleted
//   detail    a short text: the admin's note, the name of the supervisor or student ...
// The research that already exist get a first history from what is stored on them.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE research_activity (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            research_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NULL,
            action VARCHAR(30) NOT NULL,
            detail VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_research (research_id),
            CONSTRAINT fk_activity_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // 1. Who created it
    $db->exec("
        INSERT INTO research_activity (research_id, user_id, action, detail, created_at)
        SELECT r.id, r.created_by,
               CASE u.role WHEN 'student' THEN 'requested' WHEN 'teacher' THEN 'submitted' ELSE 'added' END,
               NULL, r.created_at
        FROM research r JOIN users u ON u.id = r.created_by
    ");

    // 2. The admin's decision
    $db->exec("
        INSERT INTO research_activity (research_id, user_id, action, detail, created_at)
        SELECT id, NULL, IF(status = 'rejected', 'rejected', 'approved'), review_note, reviewed_at
        FROM research WHERE reviewed_at IS NOT NULL
    ");

    // 3. The supervisor
    $db->exec("
        INSERT INTO research_activity (research_id, user_id, action, detail, created_at)
        SELECT r.id, NULL, 'supervisor_assigned', u.name, r.updated_at
        FROM research r JOIN users u ON u.id = r.supervisor_id
        WHERE r.supervisor_id IS NOT NULL AND r.status IN ('ongoing', 'completed')
    ");
};
