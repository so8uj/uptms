<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Teams (groups) of students. All the SQL of the teams is here.
 *
 * A student is "busy" when they are in an open team, or when they work on a research that is not finished
 * (requested, pending, approved or ongoing). A busy student cannot join another team.
 * A team is "busy" when it has a research that is not finished: it cannot take a second one.
 */
class Team
{
    // The smallest and the largest group/team: set on the Settings page (2 and 3 until the admin changes them).
    public static function minSize(): int
    {
        return Setting::int('team_min_size', 2);
    }

    public static function maxSize(): int
    {
        return Setting::int('team_max_size', 3);
    }

    // The research statuses that mean "not finished yet".
    private const UNFINISHED = "'requested', 'pending', 'approved', 'ongoing', 'correction'";

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ---------------------------------------------------------------------------------
    // Lists
    // ---------------------------------------------------------------------------------

    /**
     * One page of teams, newest first. $f can contain: q (team name or a member), status (requested | open | rejected | closed),
     * department_id, batch_id, member_id (only the teams this student is in), available (only open teams with no unfinished research).
     */
    public function paginate(array $f, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($f);

        $stmt = $this->db->prepare(
            "SELECT t.*, d.name AS department_name, d.code AS department_code, b.name AS batch_name, b.shift AS batch_shift,
                (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) AS member_count,
                (SELECT r.id FROM research r WHERE r.team_id = t.id AND r.deleted_at IS NULL ORDER BY r.id DESC LIMIT 1) AS research_id,
                (SELECT r.code FROM research r WHERE r.team_id = t.id AND r.deleted_at IS NULL ORDER BY r.id DESC LIMIT 1) AS research_code,
                (SELECT r.title FROM research r WHERE r.team_id = t.id AND r.deleted_at IS NULL ORDER BY r.id DESC LIMIT 1) AS research_title,
                (SELECT r.status FROM research r WHERE r.team_id = t.id AND r.deleted_at IS NULL ORDER BY r.id DESC LIMIT 1) AS research_status
             FROM teams t
             JOIN departments d ON d.id = t.department_id
             JOIN batches b ON b.id = t.batch_id
             WHERE {$where}
             ORDER BY (t.status = 'requested' OR EXISTS (SELECT 1 FROM team_change_requests c WHERE c.team_id = t.id AND c.status = 'pending')) DESC, t.id DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $this->withMembers($stmt->fetchAll());
    }

    public function count(array $f): int
    {
        [$where, $bind] = $this->filters($f);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM teams t WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    private function filters(array $f): array
    {
        $where = 't.deleted_at IS NULL';
        $bind = [];

        if (!empty($f['id'])) {
            $where .= ' AND t.id = :id';
            $bind['id'] = (int) $f['id'];
        }

        if (!empty($f['q'])) {
            $where .= ' AND (t.name LIKE :q1 OR EXISTS (
                SELECT 1 FROM team_members tm JOIN users u ON u.id = tm.student_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE tm.team_id = t.id AND (u.name LIKE :q2 OR sp.student_id LIKE :q3)))';
            $bind['q1'] = $bind['q2'] = $bind['q3'] = '%' . $f['q'] . '%';
        }

        if (in_array($f['status'] ?? '', ['requested', 'open', 'rejected', 'closed'], true)) {
            $where .= ' AND t.status = :status';
            $bind['status'] = $f['status'];
        }

        if (!empty($f['department_id'])) {
            $where .= ' AND t.department_id = :department_id';
            $bind['department_id'] = (int) $f['department_id'];
        }

        if (!empty($f['batch_id'])) {
            $where .= ' AND t.batch_id = :batch_id';
            $bind['batch_id'] = (int) $f['batch_id'];
        }

        if (!empty($f['member_id'])) {
            $where .= ' AND EXISTS (SELECT 1 FROM team_members tm WHERE tm.team_id = t.id AND tm.student_id = :member_id)';
            $bind['member_id'] = (int) $f['member_id'];
        }

        // a new group/team request, or a member change request, waits for the admin
        if (!empty($f['pending'])) {
            $where .= " AND (t.status = 'requested' OR EXISTS (SELECT 1 FROM team_change_requests c WHERE c.team_id = t.id AND c.status = 'pending'))";
        }

        if (!empty($f['available'])) {
            $where .= " AND t.status = 'open' AND NOT EXISTS (
                SELECT 1 FROM research r WHERE r.team_id = t.id AND r.deleted_at IS NULL AND r.status IN (" . self::UNFINISHED . '))';
        }

        return [$where, $bind];
    }

    // Adds 'members' => [{id, name, student_id}] to every row.
    private function withMembers(array $rows): array
    {
        if (!$rows) {
            return [];
        }

        $ids = implode(',', array_map(fn($row) => (int) $row['id'], $rows));   // whole numbers only, safe to inline

        $members = [];
        foreach ($this->db->query(
            "SELECT tm.team_id, u.id, u.name, sp.student_id
             FROM team_members tm JOIN users u ON u.id = tm.student_id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE tm.team_id IN ({$ids}) ORDER BY u.name"
        )->fetchAll() as $row) {
            $members[$row['team_id']][] = ['id' => (int) $row['id'], 'name' => $row['name'], 'student_id' => $row['student_id']];
        }

        foreach ($rows as $i => $row) {
            $rows[$i]['members'] = $members[$row['id']] ?? [];
            $rows[$i]['change'] = $this->latestChange((int) $row['id'], $rows[$i]['members']);
        }

        return $rows;
    }

    // ---------------------------------------------------------------------------------
    // Member change requests (a student asks, the admin decides)
    // ---------------------------------------------------------------------------------

    // The newest request of the team with who would be added and removed, or null. $members = the current members.
    private function latestChange(int $teamId, array $members): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.status, c.review_note, c.created_at, u.name AS requested_by_name
             FROM team_change_requests c JOIN users u ON u.id = c.requested_by
             WHERE c.team_id = :team_id ORDER BY c.id DESC LIMIT 1'
        );
        $stmt->execute(['team_id' => $teamId]);
        $change = $stmt->fetch();

        if (!$change) {
            return null;
        }

        $wanted = $this->db->prepare(
            'SELECT u.id, u.name FROM team_change_members m JOIN users u ON u.id = m.student_id WHERE m.request_id = :id ORDER BY u.name'
        );
        $wanted->execute(['id' => $change['id']]);
        $wanted = $wanted->fetchAll();

        $currentIds = array_column($members, 'id');
        $wantedIds = array_map('intval', array_column($wanted, 'id'));

        $change['member_ids'] = $wantedIds;
        $change['added'] = array_values(array_filter($wanted, fn($s) => !in_array((int) $s['id'], $currentIds, true)));
        $change['removed'] = array_values(array_filter($members, fn($s) => !in_array((int) $s['id'], $wantedIds, true)));

        return $change;
    }

    public function createChange(int $teamId, int $requestedBy, array $studentIds): int
    {
        $this->db->beginTransaction();

        try {
            $this->db->prepare('INSERT INTO team_change_requests (team_id, requested_by) VALUES (:team_id, :requested_by)')
                ->execute(['team_id' => $teamId, 'requested_by' => $requestedBy]);
            $id = (int) $this->db->lastInsertId();

            $insert = $this->db->prepare('INSERT INTO team_change_members (request_id, student_id) VALUES (:request_id, :student_id)');
            foreach (array_unique($studentIds) as $studentId) {
                $insert->execute(['request_id' => $id, 'student_id' => (int) $studentId]);
            }

            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Did the team ever ask to change its members (waiting, approved or rejected)? A team may ask only once.
    public function hasChangeRequest(int $teamId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM team_change_requests WHERE team_id = :team_id LIMIT 1');
        $stmt->execute(['team_id' => $teamId]);

        return (bool) $stmt->fetchColumn();
    }

    // The waiting request of a team (with the ids of the wanted members), or null.
    public function pendingChange(int $teamId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM team_change_requests WHERE team_id = :team_id AND status = 'pending' LIMIT 1");
        $stmt->execute(['team_id' => $teamId]);
        $change = $stmt->fetch();

        return $change ? $this->withWantedIds($change) : null;
    }

    public function findChange(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM team_change_requests WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $change = $stmt->fetch();

        return $change ? $this->withWantedIds($change) : null;
    }

    private function withWantedIds(array $change): array
    {
        $stmt = $this->db->prepare('SELECT student_id FROM team_change_members WHERE request_id = :id');
        $stmt->execute(['id' => $change['id']]);
        $change['member_ids'] = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        return $change;
    }

    // The admin's decision (status = approved | rejected) on a waiting request.
    public function reviewChange(int $id, string $status, ?string $note, int $reviewedBy): void
    {
        $this->db->prepare(
            "UPDATE team_change_requests SET status = :status, review_note = :note, reviewed_by = :by, reviewed_at = NOW()
             WHERE id = :id AND status = 'pending'"
        )->execute(['status' => $status, 'note' => $note ?: null, 'by' => $reviewedBy, 'id' => $id]);
    }

    // How many member change requests wait for the admin.
    public function changeRequestCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM team_change_requests c JOIN teams t ON t.id = c.team_id
             WHERE c.status = 'pending' AND t.deleted_at IS NULL"
        )->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $rows = $this->paginate(['id' => $id], 1, 0);

        return $rows[0] ?? null;
    }

    // ---------------------------------------------------------------------------------
    // Save
    // ---------------------------------------------------------------------------------

    // $status = 'open' when the admin makes the team, 'requested' when a student asks for it.
    public function create(string $name, int $departmentId, int $batchId, int $createdBy, array $studentIds, string $status = 'open'): int
    {
        $this->db->beginTransaction();

        try {
            $this->db->prepare(
                'INSERT INTO teams (name, department_id, batch_id, created_by, status) VALUES (:name, :department_id, :batch_id, :created_by, :status)'
            )->execute(['name' => $name, 'department_id' => $departmentId, 'batch_id' => $batchId, 'created_by' => $createdBy, 'status' => $status]);
            $id = (int) $this->db->lastInsertId();

            $this->replaceMembers($id, $studentIds);
            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Saves the new member list (and the name, when given) and copies the members to the research of the team.
    public function update(int $id, ?string $name, array $studentIds): void
    {
        $this->db->beginTransaction();

        try {
            if ($name !== null) {
                $this->db->prepare('UPDATE teams SET name = :name WHERE id = :id AND deleted_at IS NULL')
                    ->execute(['name' => $name, 'id' => $id]);
            }

            $this->replaceMembers($id, $studentIds);
            $this->syncResearch($id);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function replaceMembers(int $id, array $studentIds): void
    {
        $this->db->prepare('DELETE FROM team_members WHERE team_id = :id')->execute(['id' => $id]);

        $insert = $this->db->prepare('INSERT INTO team_members (team_id, student_id) VALUES (:team_id, :student_id)');
        foreach (array_unique($studentIds) as $studentId) {
            $insert->execute(['team_id' => $id, 'student_id' => (int) $studentId]);
        }
    }

    // The students of an unfinished research are always the members of its team.
    public function syncResearch(int $teamId): void
    {
        $research = $this->db->prepare(
            "SELECT id FROM research WHERE team_id = :team_id AND deleted_at IS NULL AND status <> 'completed'"
        );
        $research->execute(['team_id' => $teamId]);

        foreach ($research->fetchAll(PDO::FETCH_COLUMN) as $researchId) {
            $this->copyToResearch($teamId, (int) $researchId);
        }
    }

    // Replaces the students of one research with the members of the team.
    public function copyToResearch(int $teamId, int $researchId): void
    {
        $this->db->prepare('DELETE FROM research_student WHERE research_id = :id')->execute(['id' => $researchId]);
        $this->db->prepare(
            'INSERT INTO research_student (research_id, student_id) SELECT :research_id, student_id FROM team_members WHERE team_id = :team_id'
        )->execute(['research_id' => $researchId, 'team_id' => $teamId]);
    }

    public function softDelete(int $id): void
    {
        $this->db->prepare('UPDATE teams SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM team_members WHERE team_id = :id')->execute(['id' => $id]);
    }

    // The admin's decision on a requested team: status = open (approved) or rejected.
    public function review(int $id, string $status, ?string $note): void
    {
        $this->db->prepare('UPDATE teams SET status = :status, review_note = :note, reviewed_at = NOW() WHERE id = :id AND deleted_at IS NULL')
            ->execute(['status' => $status, 'note' => $note ?: null, 'id' => $id]);
    }

    // How many teams wait for the admin.
    public function requestedCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM teams WHERE status = 'requested' AND deleted_at IS NULL")->fetchColumn();
    }

    // Has this student a group that is waiting, working or finished? (For now a student has only one group.)
    public function hasGroup(int $studentId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM teams t JOIN team_members tm ON tm.team_id = t.id
             WHERE tm.student_id = :student_id AND t.status IN ('requested', 'open', 'closed') AND t.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['student_id' => $studentId]);

        return (bool) $stmt->fetchColumn();
    }

    // The research of the team was completed: the members are free again.
    public function close(int $id): void
    {
        $this->db->prepare("UPDATE teams SET status = 'closed' WHERE id = :id")->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------------
    // The rules
    // ---------------------------------------------------------------------------------

    /**
     * The active students of this department and batch, each with 'busy' = null (can join) or the reason why not:
     * already in a group/team, working on an unfinished research, or (firstGroupOnly) already finished a group/team.
     * $exceptTeamId = the team being edited (its own members are not busy because of it).
     */
    public function freeStudents(int $departmentId, int $batchId, string $search, int $exceptTeamId = 0, bool $firstGroupOnly = false, int $limit = 200): array
    {
        $sql = "SELECT u.id, u.name, sp.student_id,
                    (SELECT t.name FROM team_members tm JOIN teams t ON t.id = tm.team_id
                      WHERE tm.student_id = u.id AND t.status IN ('requested', 'open') AND t.deleted_at IS NULL AND t.id <> :ex1 LIMIT 1) AS busy_team,
                    (SELECT r.code FROM research_student rs JOIN research r ON r.id = rs.research_id
                      WHERE rs.student_id = u.id AND r.deleted_at IS NULL AND r.status IN (" . self::UNFINISHED . ")
                        AND (r.team_id IS NULL OR r.team_id <> :ex2) LIMIT 1) AS busy_research,
                    (SELECT t9.name FROM team_members tm9 JOIN teams t9 ON t9.id = tm9.team_id
                      WHERE tm9.student_id = u.id AND t9.status = 'closed' AND t9.deleted_at IS NULL LIMIT 1) AS finished_team
                FROM users u JOIN student_profiles sp ON sp.user_id = u.id
                WHERE u.role = 'student' AND u.status = 1 AND u.deleted_at IS NULL
                  AND sp.department_id = :department_id AND sp.batch_id = :batch_id";
        $bind = ['department_id' => $departmentId, 'batch_id' => $batchId, 'ex1' => $exceptTeamId, 'ex2' => $exceptTeamId];

        if ($search !== '') {
            $sql .= ' AND (u.name LIKE :q1 OR sp.student_id LIKE :q2)';
            $bind['q1'] = $bind['q2'] = '%' . $search . '%';
        }

        $stmt = $this->db->prepare($sql . " ORDER BY u.name LIMIT {$limit}");
        $stmt->execute($bind);

        $students = [];
        foreach ($stmt->fetchAll() as $row) {
            $busy = null;
            if ($row['busy_team'] !== null) {
                $busy = 'Already in the group/team "' . $row['busy_team'] . '"';
            } elseif ($row['busy_research'] !== null) {
                $busy = 'Working on research ' . $row['busy_research'];
            } elseif ($firstGroupOnly && $row['finished_team'] !== null) {
                $busy = 'Already finished a group/team';
            }

            $students[] = ['id' => (int) $row['id'], 'name' => $row['name'], 'student_id' => $row['student_id'], 'busy' => $busy];
        }

        return $students;
    }

    // Which of these students may be in the team: active, of this department and batch, and not busy elsewhere.
    public function allowedCount(array $ids, int $departmentId, int $batchId, int $exceptTeamId = 0, bool $firstGroupOnly = false): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return 0;
        }

        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users u JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.id IN ({$list}) AND u.role = 'student' AND u.status = 1 AND u.deleted_at IS NULL
               AND sp.department_id = :department_id AND sp.batch_id = :batch_id AND " . $this->notBusy('u.id')
            . ($firstGroupOnly ? ' AND ' . $this->noFinishedGroup('u.id') : '')
        );
        $stmt->execute(['department_id' => $departmentId, 'batch_id' => $batchId, 'ex1' => $exceptTeamId, 'ex2' => $exceptTeamId]);

        return (int) $stmt->fetchColumn();
    }

    // SQL: the student is not in another open team and does not work on another unfinished research.
    private function notBusy(string $column): string
    {
        return "NOT EXISTS (SELECT 1 FROM team_members tm JOIN teams t ON t.id = tm.team_id
                            WHERE tm.student_id = {$column} AND t.status IN ('requested', 'open') AND t.deleted_at IS NULL AND t.id <> :ex1)
                AND NOT EXISTS (SELECT 1 FROM research_student rs JOIN research r ON r.id = rs.research_id
                            WHERE rs.student_id = {$column} AND r.deleted_at IS NULL AND r.status IN (" . self::UNFINISHED . ')
                              AND (r.team_id IS NULL OR r.team_id <> :ex2))';
    }

    // SQL: the student has no finished group yet (for now a student has only one group).
    private function noFinishedGroup(string $column): string
    {
        return "NOT EXISTS (SELECT 1 FROM team_members tm9 JOIN teams t9 ON t9.id = tm9.team_id
                            WHERE tm9.student_id = {$column} AND t9.status = 'closed' AND t9.deleted_at IS NULL)";
    }

    // The open team of a student (the id), or null.
    public function openTeamOf(int $studentId): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT t.id FROM teams t JOIN team_members tm ON tm.team_id = t.id
             WHERE tm.student_id = :student_id AND t.status = 'open' AND t.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['student_id' => $studentId]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    public function isMember(int $teamId, int $studentId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM team_members WHERE team_id = :team_id AND student_id = :student_id');
        $stmt->execute(['team_id' => $teamId, 'student_id' => $studentId]);

        return (bool) $stmt->fetchColumn();
    }

    // Does the team work on an unfinished research? $exceptResearchId = the research being saved.
    public function isBusy(int $teamId, int $exceptResearchId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM research WHERE team_id = :team_id AND id <> :research_id AND deleted_at IS NULL
               AND status IN (' . self::UNFINISHED . ') LIMIT 1'
        );
        $stmt->execute(['team_id' => $teamId, 'research_id' => $exceptResearchId]);

        return (bool) $stmt->fetchColumn();
    }

    // Was the team ever used for a research? Such a team cannot be deleted.
    public function hasResearch(int $teamId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM research WHERE team_id = :team_id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['team_id' => $teamId]);

        return (bool) $stmt->fetchColumn();
    }

    // The id of the unfinished research of the team, or null.
    public function activeResearchId(int $teamId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM research WHERE team_id = :team_id AND deleted_at IS NULL AND status IN (' . self::UNFINISHED . ') LIMIT 1'
        );
        $stmt->execute(['team_id' => $teamId]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }
}
