<?php

// The tags of a research (many tags per research, many researches per tag).
// A row here is only a link, so it is removed for real when a tag is taken off a research.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE research_tag (
            research_id INT UNSIGNED NOT NULL,
            tag_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (research_id, tag_id),
            CONSTRAINT fk_research_tag_research FOREIGN KEY (research_id) REFERENCES research(id),
            CONSTRAINT fk_research_tag_tag FOREIGN KEY (tag_id) REFERENCES tags(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
