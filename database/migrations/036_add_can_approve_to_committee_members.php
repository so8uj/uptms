<?php

// A committee can have 7 or 8 members, but only the ones who are ticked "can approve" sign the topics and the finishes
// (any 2 of them decide). The members that exist keep the right they had.
return function (PDO $db): void {
    $db->exec("ALTER TABLE committee_members ADD COLUMN can_approve TINYINT(1) NOT NULL DEFAULT 1 AFTER role");
};
