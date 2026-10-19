<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class AcademicSession
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * One page of sessions, newest first, with the number of batches that use each one.
     * $status: '1' = active only, '0' = inactive only, '' = both.
     * $type: 'yearly', 'semester', 'trimester' or '' for all. Deleted sessions are never returned.
     */
    public function paginate(string $search, string $status, string $type, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($search, $status, $type);

        $stmt = $this->db->prepare(
            "SELECT s.*,
                (SELECT COUNT(*) FROM batches b
                    WHERE (b.admission_session_id = s.id OR b.current_session_id = s.id) AND b.deleted_at IS NULL) AS batch_count
             FROM academic_sessions s WHERE {$where} ORDER BY s.start_date DESC, s.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    public function count(string $search, string $status, string $type): int
    {
        [$where, $bind] = $this->filters($search, $status, $type);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM academic_sessions s WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    private function filters(string $search, string $status, string $type): array
    {
        $where = 's.deleted_at IS NULL';
        $bind = [];

        if ($search !== '') {
            $where .= ' AND (s.name LIKE :search OR s.year LIKE :search2)';
            $bind['search'] = '%' . $search . '%';
            $bind['search2'] = '%' . $search . '%';
        }

        if ($status === '0' || $status === '1') {
            $where .= ' AND s.status = :status';
            $bind['status'] = $status;
        }

        if (in_array($type, ['yearly', 'semester', 'trimester'], true)) {
            $where .= ' AND s.term_type = :type';
            $bind['type'] = $type;
        }

        return [$where, $bind];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM academic_sessions WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Active sessions only: used to fill the dropdowns in the batch form.
    public function options(): array
    {
        return $this->db->query(
            'SELECT id, name, term_type, year, is_current FROM academic_sessions
             WHERE deleted_at IS NULL AND status = 1 ORDER BY start_date DESC, id DESC'
        )->fetchAll();
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO academic_sessions (name, term_type, year, start_date, end_date, is_current, status)
                 VALUES (:name, :term_type, :year, :start_date, :end_date, :is_current, :status)'
            );
            $stmt->execute($data);
            $id = (int) $this->db->lastInsertId();

            if ($data['is_current'] == 1) {
                $this->makeOnlyCurrent($id, $data['term_type']);
            }

            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): void
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'UPDATE academic_sessions SET name = :name, term_type = :term_type, year = :year, start_date = :start_date,
                    end_date = :end_date, is_current = :is_current, status = :status
                 WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute($data + ['id' => $id]);

            if ($data['is_current'] == 1) {
                $this->makeOnlyCurrent($id, $data['term_type']);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // There is one current session per term type: the others of that type stop being current.
    private function makeOnlyCurrent(int $id, string $termType): void
    {
        $stmt = $this->db->prepare(
            'UPDATE academic_sessions SET is_current = 0 WHERE term_type = :term_type AND id != :id'
        );
        $stmt->execute(['term_type' => $termType, 'id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE academic_sessions SET deleted_at = NOW(), is_current = 0 WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
    }

    // How many batches use this session (as admission session or as current session).
    public function batchCount(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM batches
             WHERE (admission_session_id = :id OR current_session_id = :id2) AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'id2' => $id]);

        return (int) $stmt->fetchColumn();
    }
}
