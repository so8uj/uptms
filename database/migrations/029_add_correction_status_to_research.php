<?php

// The admin has three answers for a submitted research:
//   approved    accepted
//   correction  sent back: the owner can edit it and send it again (this is what "rejected" used to do)
//   rejected    refused for good: nothing more can be done with it
return function (PDO $db): void {
    $db->exec("
        ALTER TABLE research
            MODIFY status ENUM('requested', 'pending', 'approved', 'ongoing', 'completed', 'rejected', 'correction') NOT NULL
    ");
};
