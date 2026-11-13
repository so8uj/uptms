<?php

// A group can get its supervisor first and send its topic later.
//   assigned   the group and the supervisor are set and the work has started, but no topic was approved yet
// So the topic fields of the research may be empty until the group sends the topic.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE research
            MODIFY status ENUM('requested', 'pending', 'approved', 'ongoing', 'completed', 'rejected', 'correction', 'assigned') NOT NULL,
            MODIFY title VARCHAR(255) NULL,
            MODIFY abstract TEXT NULL,
            MODIFY category_id INT UNSIGNED NULL
    ");
};
