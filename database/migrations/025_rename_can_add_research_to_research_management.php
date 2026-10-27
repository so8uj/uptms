<?php

// teacher_profiles.can_add_research becomes research_management.
// A teacher with research_management = 1 may add research and also manage the research:
// approve or reject research, assign supervisors, complete research, approve groups/teams and add batches.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE teacher_profiles
            CHANGE can_add_research research_management TINYINT(1) NOT NULL DEFAULT 0
    ");
};
