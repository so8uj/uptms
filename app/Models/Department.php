<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Department
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * One page of departments, newest first, with the number of students in each.
     * $status: '1' = active only, '0' = inactive only, '' = both. Deleted departments are never returned.
     */
    public function paginate(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare(
            "SELECT d.*,
                (SELECT COUNT(*) FROM student_profiles sp
                    JOIN users u ON u.id = sp.user_id AND u.deleted_at IS NULL
                    WHERE sp.department_id = d.id) AS student_count
             FROM departments d WHERE {$where} ORDER BY d.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    public function count(string $search, string $status): int
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM departments d WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    private function filters(string $search, string $status): array
    {
        $where = 'd.deleted_at IS NULL';
        $bind = [];

        if ($search !== '') {
            $where .= ' AND (d.name LIKE :search OR d.code LIKE :search2 OR d.description LIKE :search3)';
            $bind['search'] = '%' . $search . '%';
            $bind['search2'] = '%' . $search . '%';
            $bind['search3'] = '%' . $search . '%';
        }

        if ($status === '0' || $status === '1') {
            $where .= ' AND d.status = :status';
            $bind['status'] = $status;
        }

        return [$where, $bind];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM departments WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Active departments only: used to fill the dropdowns in the other forms.
    public function options(): array
    {
        return $this->db->query(
            'SELECT id, name, code FROM departments WHERE deleted_at IS NULL AND status = 1 ORDER BY name'
        )->fetchAll();
    }

    public function create(string $name, string $code, string $description, int $status): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO departments (name, code, description, status) VALUES (:name, :code, :description, :status)'
        );
        $stmt->execute(['name' => $name, 'code' => $code, 'description' => $description ?: null, 'status' => $status]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $code, string $description, int $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE departments SET name = :name, code = :code, description = :description, status = :status
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['name' => $name, 'code' => $code, 'description' => $description ?: null, 'status' => $status, 'id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE departments SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    /**
     * What still uses this department. A department that is in use cannot be deleted.
     * Returns for example ['batches' => 2, 'teachers' => 1, 'students' => 14]
     */
    public function usage(int $id): array
    {
        $count = function (string $sql) use ($id) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return (int) $stmt->fetchColumn();
        };

        return [
            'batches'  => $count('SELECT COUNT(*) FROM batches WHERE department_id = :id AND deleted_at IS NULL'),
            'teachers' => $count('SELECT COUNT(*) FROM teacher_profiles tp JOIN users u ON u.id = tp.user_id
                                  WHERE tp.department_id = :id AND u.deleted_at IS NULL'),
            'students' => $count('SELECT COUNT(*) FROM student_profiles sp JOIN users u ON u.id = sp.user_id
                                  WHERE sp.department_id = :id AND u.deleted_at IS NULL'),
        ];
    }
}
