<?php

namespace App\Models;

use App\Config\Database;
use PDO;

// The comments on a research (table research_comments). Deleted comments are never returned.
class ResearchComment {

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Newest first, with the name and role of the writer.
    public function forResearch(int $researchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.user_id, c.milestone_id, c.body, c.created_at, u.name AS user_name, u.role AS user_role
             FROM research_comments c JOIN users u ON u.id = c.user_id
             WHERE c.research_id = :research_id AND c.deleted_at IS NULL
             ORDER BY c.id DESC'
        );
        $stmt->execute(['research_id' => $researchId]);

        return $stmt->fetchAll();
    }

    // The latest comments of several research for a list page, with the research.
    public function forResearchIds(array $researchIds, int $limit = 100): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return $this->db->query(
            "SELECT c.id, c.research_id, c.body, c.created_at, u.name AS user_name, u.role AS user_role,
                    r.code AS research_code, r.title AS research_title, m.title AS milestone_title
             FROM research_comments c
             JOIN users u ON u.id = c.user_id
             JOIN research r ON r.id = c.research_id
             LEFT JOIN milestones m ON m.id = c.milestone_id
             WHERE c.research_id IN ({$list}) AND c.deleted_at IS NULL
             ORDER BY c.id DESC LIMIT {$limit}"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM research_comments WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // $milestoneId is set when the comment is the feedback on a milestone.
    public function create(int $researchId, int $userId, string $body, ?int $milestoneId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO research_comments (research_id, milestone_id, user_id, body) VALUES (:research_id, :milestone_id, :user_id, :body)'
        );
        $stmt->execute(['research_id' => $researchId, 'milestone_id' => $milestoneId, 'user_id' => $userId, 'body' => $body]);

        return (int) $this->db->lastInsertId();
    }

    public function softDelete(int $id): void
    {
        $this->db->prepare('UPDATE research_comments SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL')->execute(['id' => $id]);
    }
}
