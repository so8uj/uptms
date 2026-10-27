<?php

// 1. A comment can belong to a milestone: that is the feedback on the milestone (milestone_id empty = a general comment).
// 2. research_seen remembers when a person last looked at a research, so the dashboard can say "you have new things":
//    everything other people did after that time is new.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE research_comments
            ADD milestone_id INT UNSIGNED NULL AFTER research_id,
            ADD CONSTRAINT fk_comments_milestone FOREIGN KEY (milestone_id) REFERENCES milestones(id)
    ");

    $db->exec("
        CREATE TABLE research_seen (
            user_id INT UNSIGNED NOT NULL,
            research_id INT UNSIGNED NOT NULL,
            seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, research_id),
            CONSTRAINT fk_seen_user FOREIGN KEY (user_id) REFERENCES users(id),
            CONSTRAINT fk_seen_research FOREIGN KEY (research_id) REFERENCES research(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
