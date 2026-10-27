<?php

// One row for every notification a person gets (the bell on the top bar).
// read_at = NULL means unread. link = the page that opens when the notification is clicked.
return function (PDO $db): void {
    $db->exec("
        CREATE TABLE notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            type VARCHAR(40) NOT NULL,
            title VARCHAR(150) NOT NULL,
            body VARCHAR(500) NULL,
            link VARCHAR(255) NULL,
            read_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user (user_id, read_at, id),
            CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
