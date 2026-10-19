<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Batch
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * One page of batches, newest first, with the department name and the number of students.
     * $status: '1' = active only, '0' = inactive only, '' = both. Deleted batches are never returned.
     */
    public function paginate(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare(
            "SELECT b.*, d.name AS department_name, d.code AS department_code,
                asess.name AS admission_session_name, csess.name AS current_session_name,
                (SELECT COUNT(*) FROM student_profiles sp
                    JOIN users u ON u.id = sp.user_id AND u.deleted_at IS NULL
                    WHERE sp.batch_id = b.id) AS student_count
             FROM batches b
             JOIN departments d ON d.id = b.department_id
             JOIN academic_sessions asess ON asess.id = b.admission_session_id
             LEFT JOIN academic_sessions csess ON csess.id = b.current_session_id
             WHERE {$where} ORDER BY b.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    public function count(string $search, string $status): int
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM batches b WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    private function filters(string $search, string $status): array
    {
        $where = 'b.deleted_at IS NULL';
        $bind = [];

        if ($search !== '') {
            $where .= ' AND (b.name LIKE :search OR b.code LIKE :search2 OR b.shift LIKE :search3)';
            $bind['search'] = '%' . $search . '%';
            $bind['search2'] = '%' . $search . '%';
            $bind['search3'] = '%' . $search . '%';
        }

        if ($status === '0' || $status === '1') {
            $where .= ' AND b.status = :status';
            $bind['status'] = $status;
        }

        return [$where, $bind];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM batches WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Active batches only: used to fill the dropdown in the student form.
    public function options(): array
    {
        return $this->db->query(
            'SELECT id, name, department_id, shift, term_system, total_terms, current_term_no
             FROM batches WHERE deleted_at IS NULL AND status = 1 ORDER BY name'
        )->fetchAll();
    }

    // $data = name, code, shift, term_system, total_terms, admission_session_id, current_session_id,
    //         current_term_no, department_id, description, status
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO batches (name, code, shift, term_system, total_terms, admission_session_id, current_session_id,
                                  current_term_no, department_id, description, status)
             VALUES (:name, :code, :shift, :term_system, :total_terms, :admission_session_id, :current_session_id,
                     :current_term_no, :department_id, :description, :status)'
        );
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE batches SET name = :name, code = :code, shift = :shift, term_system = :term_system,
                total_terms = :total_terms, admission_session_id = :admission_session_id,
                current_session_id = :current_session_id, current_term_no = :current_term_no,
                department_id = :department_id, description = :description, status = :status
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute($data + ['id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE batches SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    // How many students are in this batch. A batch with students cannot be deleted.
    public function studentCount(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM student_profiles sp JOIN users u ON u.id = sp.user_id
             WHERE sp.batch_id = :id AND u.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }
}
