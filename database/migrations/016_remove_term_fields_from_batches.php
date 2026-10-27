<?php

// A batch no longer keeps its own term system, current session or current term.
//   The term system comes from the admission session (its type: yearly, semester or trimester).
//   The current session and the current term belong to the student, not to the batch.
// A batch keeps: name, code, department, shift, program length (total_terms), admission session.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE batches
            DROP FOREIGN KEY fk_batches_current_session,
            DROP COLUMN term_system,
            DROP COLUMN current_session_id,
            DROP COLUMN current_term_no
    ");
};
