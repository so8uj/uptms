<?php

// A batch now knows how it runs:
//   shift                  Regular or Evening
//   term_system            yearly, semester or trimester
//   total_terms            the program length in those terms (4 years, 6 semesters, 12 trimesters ...)
//   admission_session_id   the session the batch was admitted in (this replaces the old free-text "session")
//   current_session_id     the session the batch is running now (can stay empty until it is set)
//   current_term_no        the term the batch is in now: 1 .. total_terms
//
// The old "session" text column is dropped. Run this on an empty batches table (or move the data first),
// because the new required columns have no value for old rows.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE batches
            DROP COLUMN session,
            ADD shift ENUM('Regular', 'Evening') NOT NULL DEFAULT 'Regular' AFTER code,
            ADD term_system ENUM('yearly', 'semester', 'trimester') NOT NULL AFTER shift,
            ADD total_terms TINYINT UNSIGNED NOT NULL AFTER term_system,
            ADD admission_session_id INT UNSIGNED NOT NULL AFTER total_terms,
            ADD current_session_id INT UNSIGNED NULL AFTER admission_session_id,
            ADD current_term_no TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER current_session_id,
            ADD CONSTRAINT fk_batches_admission_session FOREIGN KEY (admission_session_id) REFERENCES academic_sessions(id),
            ADD CONSTRAINT fk_batches_current_session FOREIGN KEY (current_session_id) REFERENCES academic_sessions(id)
    ");
};
