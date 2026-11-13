<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The thesis committees (tables committees and committee_members).
 * A committee with department_id NULL works for the whole university, otherwise for one department.
 * Only the members of an active committee decide on a research topic, a request to finish and a group/team.
 */
class Committee
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // One page of committees, newest first, with their department name and their members.
    public function paginate(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare(
            "SELECT c.*, d.name AS department_name FROM committees c
             LEFT JOIN departments d ON d.id = c.department_id
             WHERE {$where} ORDER BY c.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $this->withMembers($stmt->fetchAll());
    }

    public function count(string $search, string $status): int
    {
        [$where, $bind] = $this->filters($search, $status);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM committees c WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    private function filters(string $search, string $status): array
    {
        $where = 'c.deleted_at IS NULL';
        $bind = [];

        if ($search !== '') {
            $where .= ' AND c.name LIKE :search';
            $bind['search'] = '%' . $search . '%';
        }

        if ($status === '0' || $status === '1') {
            $where .= ' AND c.status = :status';
            $bind['status'] = $status;
        }

        return [$where, $bind];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, d.name AS department_name FROM committees c
             LEFT JOIN departments d ON d.id = c.department_id
             WHERE c.id = :id AND c.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->withMembers([$row])[0] : null;
    }

    // Adds 'members' (teacher_id, name, employee_id, role, can_approve) to every committee. Deleted or inactive teachers are left out.
    private function withMembers(array $rows): array
    {
        if (!$rows) {
            return [];
        }

        $ids = implode(',', array_map(fn($row) => (int) $row['id'], $rows));   // whole numbers only, safe to inline

        $members = $this->db->query(
            "SELECT cm.committee_id, cm.teacher_id, cm.role, cm.can_approve, u.name, tp.employee_id
             FROM committee_members cm
             JOIN users u ON u.id = cm.teacher_id AND u.status = 1 AND u.deleted_at IS NULL
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             WHERE cm.committee_id IN ({$ids}) ORDER BY cm.role = 'chair' DESC, u.name"
        )->fetchAll();

        $byCommittee = [];
        foreach ($members as $member) {
            $byCommittee[(int) $member['committee_id']][] = [
                'teacher_id'  => (int) $member['teacher_id'],
                'name'        => $member['name'],
                'employee_id' => $member['employee_id'],
                'role'        => $member['role'],
                'can_approve' => (int) $member['can_approve'] === 1,
            ];
        }

        foreach ($rows as $i => $row) {
            $rows[$i]['members'] = $byCommittee[(int) $row['id']] ?? [];
        }

        return $rows;
    }

    // $members = list of ['teacher_id' => .., 'role' => 'chair'|'member', 'can_approve' => bool]
    public function create(string $name, ?int $departmentId, int $status, array $members): int
    {
        $this->db->beginTransaction();

        try {
            $this->db->prepare('INSERT INTO committees (name, department_id, status) VALUES (:name, :department_id, :status)')
                ->execute(['name' => $name, 'department_id' => $departmentId, 'status' => $status]);
            $id = (int) $this->db->lastInsertId();

            $this->replaceMembers($id, $members);
            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, string $name, ?int $departmentId, int $status, array $members): void
    {
        $this->db->beginTransaction();

        try {
            $this->db->prepare('UPDATE committees SET name = :name, department_id = :department_id, status = :status WHERE id = :id')
                ->execute(['name' => $name, 'department_id' => $departmentId, 'status' => $status, 'id' => $id]);

            $this->replaceMembers($id, $members);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function replaceMembers(int $id, array $members): void
    {
        $this->db->prepare('DELETE FROM committee_members WHERE committee_id = :id')->execute(['id' => $id]);

        $insert = $this->db->prepare('INSERT INTO committee_members (committee_id, teacher_id, role, can_approve) VALUES (:committee_id, :teacher_id, :role, :can_approve)');
        foreach ($members as $member) {
            $insert->execute(['committee_id' => $id, 'teacher_id' => (int) $member['teacher_id'], 'role' => $member['role'], 'can_approve' => !empty($member['can_approve']) ? 1 : 0]);
        }
    }

    public function softDelete(int $id): void
    {
        $this->db->prepare('UPDATE committees SET deleted_at = NOW() WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------------
    // Who decides
    // ---------------------------------------------------------------------------------

    // Is this teacher a member of an active committee?
    public function isMember(int $teacherId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM committee_members cm JOIN committees c ON c.id = cm.committee_id
             WHERE cm.teacher_id = :teacher_id AND c.status = 1 AND c.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['teacher_id' => $teacherId]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * May this teacher decide for a group/research of this department?
     * Yes when an active committee of the whole university, or of that department, has the teacher as a member.
     * $departmentId = null (unknown) is answered by "is a member of any active committee".
     */
    public function canDecide(int $teacherId, ?int $departmentId): bool
    {
        if ($departmentId === null) {
            return $this->isMember($teacherId);
        }

        $stmt = $this->db->prepare(
            'SELECT 1 FROM committee_members cm JOIN committees c ON c.id = cm.committee_id
             WHERE cm.teacher_id = :teacher_id AND c.status = 1 AND c.deleted_at IS NULL
               AND (c.department_id IS NULL OR c.department_id = :department_id) LIMIT 1'
        );
        $stmt->execute(['teacher_id' => $teacherId, 'department_id' => $departmentId]);

        return (bool) $stmt->fetchColumn();
    }

    // The departments this teacher decides for: null = every department (a committee of the whole university), else the ids.
    public function departmentsOf(int $teacherId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.department_id FROM committee_members cm JOIN committees c ON c.id = cm.committee_id
             WHERE cm.teacher_id = :teacher_id AND c.status = 1 AND c.deleted_at IS NULL'
        );
        $stmt->execute(['teacher_id' => $teacherId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (in_array(null, $ids, true)) {
            return null;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    // The active teachers who decide for this department (null = any): they get the notifications about requests.
    public function audience(?int $departmentId = null): array
    {
        $sql = "SELECT DISTINCT cm.teacher_id FROM committee_members cm
                JOIN committees c ON c.id = cm.committee_id AND c.status = 1 AND c.deleted_at IS NULL
                JOIN users u ON u.id = cm.teacher_id AND u.status = 1 AND u.deleted_at IS NULL AND cm.can_approve = 1";
        $bind = [];

        if ($departmentId !== null) {
            $sql .= ' WHERE c.department_id IS NULL OR c.department_id = :department_id';
            $bind['department_id'] = $departmentId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // The teachers who sign for this department (null = any): [{teacher_id, name}]. They are the members of the active
    // committees of the whole university and of that department who are ticked "can approve".
    public function membersOf(?int $departmentId): array
    {
        $sql = "SELECT DISTINCT cm.teacher_id, u.name
                FROM committee_members cm
                JOIN committees c ON c.id = cm.committee_id AND c.status = 1 AND c.deleted_at IS NULL
                JOIN users u ON u.id = cm.teacher_id AND u.status = 1 AND u.deleted_at IS NULL
                WHERE cm.can_approve = 1";
        $bind = [];

        if ($departmentId !== null) {
            $sql .= ' AND (c.department_id IS NULL OR c.department_id = :department_id)';
            $bind['department_id'] = $departmentId;
        }

        $stmt = $this->db->prepare($sql . ' ORDER BY u.name');
        $stmt->execute($bind);

        return array_map(fn($row) => ['teacher_id' => (int) $row['teacher_id'], 'name' => $row['name']], $stmt->fetchAll());
    }

    // How many active teachers (not deleted) are in this list of ids. Used to reject a tampered member list.
    public function countActiveTeachers(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return 0;
        }

        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE id IN ({$list}) AND role = 'teacher' AND status = 1 AND deleted_at IS NULL"
        )->fetchColumn();
    }
}
