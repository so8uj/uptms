<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The notifications of the bell (table notifications). A person only ever reads and changes their own rows.
 */
class Notification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function add(int $userId, string $type, string $title, ?string $body, ?string $link): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, body, link) VALUES (:user_id, :type, :title, :body, :link)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => mb_substr($title, 0, 150),
            'body'    => $body !== null ? mb_substr($body, 0, 500) : null,
            'link'    => $link,
        ]);
    }

    // One page, newest first. seconds_ago is calculated by MySQL, so the browser clock does not matter.
    public function paginate(int $userId, bool $unreadOnly, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, title, body, link, read_at IS NOT NULL AS is_read, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS seconds_ago
             FROM notifications WHERE user_id = :user_id' . ($unreadOnly ? ' AND read_at IS NULL' : '') .
            " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(function ($row) {
            $row['id'] = (int) $row['id'];
            $row['is_read'] = (int) $row['is_read'] === 1;
            $row['seconds_ago'] = (int) $row['seconds_ago'];

            return $row;
        }, $stmt->fetchAll());
    }

    public function count(int $userId, bool $unreadOnly): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id' . ($unreadOnly ? ' AND read_at IS NULL' : '')
        );
        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    // Marks one notification of this person as read (or unread again).
    public function setRead(int $id, int $userId, bool $read): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET read_at = ' . ($read ? 'COALESCE(read_at, NOW())' : 'NULL') . ' WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->db->prepare('UPDATE notifications SET read_at = NOW() WHERE user_id = :user_id AND read_at IS NULL')
            ->execute(['user_id' => $userId]);
    }

    // The active users among these ids, with their role.
    public function activeUsers(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }

        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return $this->db->query(
            "SELECT id, role FROM users WHERE id IN ({$list}) AND status = 1 AND deleted_at IS NULL"
        )->fetchAll();
    }

    // The people who decide: the active admins and the teachers with the research management permission.
    public function adminAudience(): array
    {
        return array_map('intval', $this->db->query(
            "SELECT u.id FROM users u LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             WHERE u.status = 1 AND u.deleted_at IS NULL AND (u.role = 'admin' OR (u.role = 'teacher' AND tp.research_management = 1))"
        )->fetchAll(PDO::FETCH_COLUMN));
    }
}
