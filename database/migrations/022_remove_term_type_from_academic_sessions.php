<?php

// An academic session no longer has a term type (yearly, semester, trimester).
// It is only a named period (for example "Spring 2026") and there is one current session for the whole university.
return function (PDO $db): void {
    // If several sessions were current (one per type before), only the newest one stays current.
    $db->exec("
        UPDATE academic_sessions SET is_current = 0
        WHERE is_current = 1
          AND id <> (SELECT keep_id FROM (SELECT MAX(id) AS keep_id FROM academic_sessions WHERE is_current = 1) AS newest)
    ");

    $db->exec("ALTER TABLE academic_sessions DROP INDEX idx_sessions_type, DROP COLUMN term_type");
};
