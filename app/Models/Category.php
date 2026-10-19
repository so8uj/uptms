<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * One page of categories, newest first.
     * $status: '1' = active only, '0' = inactive only, '' = both.
     * Deleted categories (deleted_at is set) are never returned.
     */
    public function paginate(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare(
            "SELECT * FROM categories WHERE {$where} ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    // How many categories match the same filters (used for the page numbers).
    public function count(string $search, string $status): int
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE {$where}");
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
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $description, int $status): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, description, status) VALUES (:name, :description, :status)'
        );
        $stmt->execute(['name' => $name, 'description' => $description ?: null, 'status' => $status]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $description, int $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE categories SET name = :name, description = :description, status = :status
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['name' => $name, 'description' => $description ?: null, 'status' => $status, 'id' => $id]);
    }

    // Soft delete: the row stays in the table, it only gets a deleted date.
    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE categories SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    // How many research items use this category. A category that is in use cannot be deleted.
    public function countResearch(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM research WHERE category_id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    // Active categories only: used to fill the category dropdown in the research forms.
    public function options(): array
    {
        return $this->db->query(
            'SELECT id, name FROM categories WHERE deleted_at IS NULL AND status = 1 ORDER BY name'
        )->fetchAll();
    }

    // A category can be chosen for a research only when it exists, is not deleted and is active.
    public function isActive(int $id): bool
    {
        $category = $this->find($id);

        return $category !== null && (int) $category['status'] === 1;
    }
}
