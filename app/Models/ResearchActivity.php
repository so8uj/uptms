<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The history of a research (table research_activity): one row for every action.
 * Nothing here is ever edited or deleted.
 */
class ResearchActivity {

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Writes one line of history. $detail is a short text such as the admin's note or a person's name.
    public function add(int $researchId, ?int $userId, string $action, ?string $detail = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO research_activity (research_id, user_id, action, detail) VALUES (:research_id, :user_id, :action, :detail)'
        );
        $stmt->execute([
            'research_id' => $researchId,
            'user_id'     => $userId,
            'action'      => $action,
            'detail'      => $detail !== null && $detail !== '' ? mb_substr($detail, 0, 500) : null,
        ]);

        // the notification bell of the people of this research
        \App\Services\Notifier::forActivity($researchId, $userId, $action, $detail);
    }

    // ---------------------------------------------------------------------------------
    // "What is new": what other people did after I last looked (table research_seen)
    // ---------------------------------------------------------------------------------

    // I looked at these research now: whatever happened before this moment is not new any more.
    public function markSeen(int $userId, array $researchIds): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO research_seen (user_id, research_id, seen_at) VALUES (:user_id, :research_id, NOW())
             ON DUPLICATE KEY UPDATE seen_at = NOW()'
        );

        foreach (array_unique($researchIds) as $researchId) {
            $stmt->execute(['user_id' => $userId, 'research_id' => (int) $researchId]);
        }
    }

    /**
     * The new things for one person, counted by research and by action.
     * New = done by somebody else, after the person last looked at that research (or never looked).
     * Returns rows: research_id, code, title, status, action, total
     */
    public function unseenFor(int $userId, array $researchIds, array $actions): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids || !$actions) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline
        $marks = implode(',', array_fill(0, count($actions), '?'));

        $stmt = $this->db->prepare(
            "SELECT a.research_id, r.code, r.title, r.status, a.action, COUNT(*) AS total
             FROM research_activity a
             JOIN research r ON r.id = a.research_id AND r.deleted_at IS NULL
             LEFT JOIN research_seen s ON s.research_id = a.research_id AND s.user_id = ?
             WHERE a.research_id IN ({$list})
               AND (a.user_id IS NULL OR a.user_id <> ?)
               AND a.action IN ({$marks})
               AND (s.seen_at IS NULL OR a.created_at > s.seen_at)
             GROUP BY a.research_id, r.code, r.title, r.status, a.action
             ORDER BY MAX(a.created_at) DESC"
        );
        $stmt->execute(array_merge([$userId, $userId], $actions));

        return $stmt->fetchAll();
    }

    // When something happened last in each research: [research_id => 'Y-m-d H:i:s']
    public function lastTimes(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $result = [];
        foreach ($this->db->query(
            "SELECT research_id, MAX(created_at) AS last_at FROM research_activity WHERE research_id IN ({$list}) GROUP BY research_id"
        )->fetchAll() as $row) {
            $result[(int) $row['research_id']] = $row['last_at'];
        }

        return $result;
    }

    // The latest actions of several research for a list page (newest first), with the research.
    public function forResearchIds(array $researchIds, int $limit = 100): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return $this->db->query(
            "SELECT a.id, a.research_id, a.action, a.detail, a.created_at, u.name AS user_name, u.role AS user_role,
                    r.code AS research_code, r.title AS research_title
             FROM research_activity a
             JOIN research r ON r.id = a.research_id
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.research_id IN ({$list}) AND r.status IN ('ongoing', 'completed')
             ORDER BY a.created_at DESC, a.id DESC LIMIT {$limit}"
        )->fetchAll();
    }

    // The whole history of one research, oldest first, with the name and role of the person who did each action.
    public function forResearch(int $researchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.action, a.detail, a.created_at, a.user_id, u.name AS user_name, u.role AS user_role
             FROM research_activity a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.research_id = :research_id
             ORDER BY a.created_at, a.id'
        );
        $stmt->execute(['research_id' => $researchId]);

        return $stmt->fetchAll();
    }
}
