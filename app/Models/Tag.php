<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Tag
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * One page of tags, newest first.
     * $status: '1' = active only, '0' = inactive only, '' = both.
     * Deleted tags (deleted_at is set) are never returned.
     */
    public function paginate(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare(
            "SELECT * FROM tags WHERE {$where} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    // How many tags match the same filters (used for the page numbers).
    public function count(string $search, string $status): int
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tags WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    // Builds the WHERE part once, so paginate() and count() always agree.
    private function filters(string $search, string $status): array
    {
        $where = 'deleted_at IS NULL';
        $bind = [];

        if ($search !== '') {
            $where .= ' AND (name LIKE :search OR description LIKE :search2)';
            $bind['search'] = '%' . $search . '%';
            $bind['search2'] = '%' . $search . '%';
        }

        if ($status === '0' || $status === '1') {
            $where .= ' AND status = :status';
            $bind['status'] = $status;
        }

        return [$where, $bind];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tags WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $description, int $status): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tags (name, description, status) VALUES (:name, :description, :status)'
        );
        $stmt->execute(['name' => $name, 'description' => $description ?: null, 'status' => $status]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $description, int $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE tags SET name = :name, description = :description, status = :status
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['name' => $name, 'description' => $description ?: null, 'status' => $status, 'id' => $id]);
    }

    // Soft delete: the row stays in the table, it only gets a deleted date.
    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE tags SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    // How many research items use this tag. A tag that is in use cannot be deleted.
    public function countResearch(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM research_tag rt JOIN research r ON r.id = rt.research_id WHERE rt.tag_id = :id AND r.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    // Active tags only: used to fill the tag list in the research forms.
    public function options(): array
    {
        return $this->db->query(
            'SELECT id, name FROM tags WHERE deleted_at IS NULL AND status = 1 ORDER BY name'
        )->fetchAll();
    }

    // How many of these tag ids are real, active tags. Used to reject a tampered list.
    public function countActive(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return 0;
        }

        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return (int) $this->db->query(
            "SELECT COUNT(*) FROM tags WHERE id IN ({$list}) AND deleted_at IS NULL AND status = 1"
        )->fetchColumn();
    }
}
