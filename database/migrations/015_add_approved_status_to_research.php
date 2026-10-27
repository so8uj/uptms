<?php

// A student's request now has a step between "requested" and "ongoing":
//   approved   the admin accepted the topic. The student adds the team members, then the admin assigns the supervisor.
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE research
            MODIFY status ENUM('requested', 'pending', 'approved', 'ongoing', 'completed', 'rejected') NOT NULL
    ");
};
