<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Milestones and their tasks (tables milestones and tasks).
 * Deleted rows are never returned: every read has deleted_at IS NULL.
 */
class Milestone {

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ---------------------------------------------------------------------------------
    // Milestones
    // ---------------------------------------------------------------------------------

    // All milestones of a research (oldest first), each with its 'tasks' and the name of its creator.
    public function forResearch(int $researchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.id, m.title, m.description, m.due_date, m.created_by, m.created_at, u.name AS creator_name, u.role AS creator_role
             FROM milestones m JOIN users u ON u.id = m.created_by
             WHERE m.research_id = :research_id AND m.deleted_at IS NULL
             ORDER BY m.due_date IS NULL, m.due_date, m.id'
        );
        $stmt->execute(['research_id' => $researchId]);
        $milestones = $stmt->fetchAll();

        if (!$milestones) {
            return [];
        }

        $ids = implode(',', array_map(fn($row) => (int) $row['id'], $milestones));   // whole numbers only, safe to inline

        $tasks = [];
        foreach ($this->db->query(
            "SELECT t.id, t.milestone_id, t.title, t.is_done, t.completed_at, t.created_by, t.created_at,
                    cu.name AS creator_name, du.name AS completed_by_name
             FROM tasks t
             JOIN users cu ON cu.id = t.created_by
             LEFT JOIN users du ON du.id = t.completed_by
             WHERE t.milestone_id IN ({$ids}) AND t.deleted_at IS NULL ORDER BY t.id"
        )->fetchAll() as $row) {
            $row['is_done'] = (int) $row['is_done'];
            $tasks[$row['milestone_id']][] = $row;
        }

        foreach ($milestones as $i => $milestone) {
            $milestones[$i]['tasks'] = $tasks[$milestone['id']] ?? [];
        }

        return $milestones;
    }

    // The milestones of several research for a list page: with the research and how many tasks are done.
    public function forResearchIds(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $rows = $this->db->query(
            "SELECT m.id, m.research_id, m.title, m.description, m.due_date, m.created_at, u.name AS creator_name,
                    r.code AS research_code, r.title AS research_title,
                    COUNT(t.id) AS tasks, COALESCE(SUM(t.is_done), 0) AS done
             FROM milestones m
             JOIN users u ON u.id = m.created_by
             JOIN research r ON r.id = m.research_id
             LEFT JOIN tasks t ON t.milestone_id = m.id AND t.deleted_at IS NULL
             WHERE m.research_id IN ({$list}) AND m.deleted_at IS NULL
             GROUP BY m.id ORDER BY m.due_date IS NULL, m.due_date, m.id"
        )->fetchAll();

        foreach ($rows as $i => $row) {
            $rows[$i]['tasks'] = (int) $row['tasks'];
            $rows[$i]['done'] = (int) $row['done'];
        }

        return $rows;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM milestones WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // $data = research_id, title, description, due_date, created_by
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO milestones (research_id, title, description, due_date, created_by)
             VALUES (:research_id, :title, :description, :due_date, :created_by)'
        );
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    // The milestone and its tasks disappear together.
    public function softDelete(int $id): void
    {
        $this->db->prepare('UPDATE tasks SET deleted_at = NOW() WHERE milestone_id = :id AND deleted_at IS NULL')->execute(['id' => $id]);
        $this->db->prepare('UPDATE milestones SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL')->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------------
    // Tasks
    // ---------------------------------------------------------------------------------

    // One task with the research it belongs to (through its milestone).
    public function findTask(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, m.research_id FROM tasks t
             JOIN milestones m ON m.id = t.milestone_id AND m.deleted_at IS NULL
             WHERE t.id = :id AND t.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function createTask(int $milestoneId, string $title, int $userId): int
    {
        $stmt = $this->db->prepare('INSERT INTO tasks (milestone_id, title, created_by) VALUES (:milestone_id, :title, :created_by)');
        $stmt->execute(['milestone_id' => $milestoneId, 'title' => $title, 'created_by' => $userId]);

        return (int) $this->db->lastInsertId();
    }

    // Finished (with who and when) or opened again.
    public function setTaskDone(int $id, bool $done, int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tasks SET is_done = :done, completed_by = :by, completed_at = IF(:done2 = 1, NOW(), NULL)
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['done' => $done ? 1 : 0, 'done2' => $done ? 1 : 0, 'by' => $done ? $userId : null, 'id' => $id]);
    }

    public function softDeleteTask(int $id): void
    {
        $this->db->prepare('UPDATE tasks SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL')->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------------
    // Numbers
    // ---------------------------------------------------------------------------------

    // For a list of research ids: [research_id => ['milestones' => n, 'tasks' => n, 'done' => n]]
    public function progressFor(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $result = [];
        foreach ($this->db->query(
            "SELECT m.research_id, COUNT(DISTINCT m.id) AS milestones, COUNT(t.id) AS tasks, COALESCE(SUM(t.is_done), 0) AS done
             FROM milestones m LEFT JOIN tasks t ON t.milestone_id = m.id AND t.deleted_at IS NULL
             WHERE m.research_id IN ({$list}) AND m.deleted_at IS NULL GROUP BY m.research_id"
        )->fetchAll() as $row) {
            $result[(int) $row['research_id']] = ['milestones' => (int) $row['milestones'], 'tasks' => (int) $row['tasks'], 'done' => (int) $row['done']];
        }

        return $result;
    }
}
