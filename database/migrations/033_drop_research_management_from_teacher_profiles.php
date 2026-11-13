<?php

// The research management permission of a teacher is replaced by the thesis committees (migration 031 already moved the
// teachers who had it into a committee). Only the committee members decide on topics, finishes and groups now.
return function (PDO $db): void {
    $db->exec("ALTER TABLE teacher_profiles DROP COLUMN research_management");
};
