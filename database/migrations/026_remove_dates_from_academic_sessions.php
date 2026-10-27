<?php

// An academic session is only a name and a year (for example "Spring 2026").
// The start date and the end date are not kept: the university does not have them.
return function (PDO $db): void {
    $db->exec("ALTER TABLE academic_sessions DROP COLUMN start_date, DROP COLUMN end_date");
};
