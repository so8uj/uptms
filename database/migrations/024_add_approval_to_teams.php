<?php

// A group/team that a student makes must be approved by the admin.
// status: requested (waiting for the admin), open (approved and working), rejected (the admin said no),
//         closed (its research was completed).
// A team the admin makes is open at once. review_note says why a request was rejected.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE teams
            MODIFY status ENUM('requested', 'open', 'rejected', 'closed') NOT NULL DEFAULT 'open',
            ADD COLUMN review_note VARCHAR(500) NULL AFTER status,
            ADD COLUMN reviewed_at TIMESTAMP NULL DEFAULT NULL AFTER review_note
    ");
};
