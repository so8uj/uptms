<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The queries of the reports. Each report is one SELECT; its column names are the keys of the answer.
 * A column that starts with an underscore (_id) is only for the code and is not shown.
 * Deleted rows (deleted_at) are never counted.
 */
class Report
{
    // report key => name
    public const TYPES = [
        'research'    => 'Research',
        'teams'       => 'Groups/Teams',
        'students'    => 'Students',
        'supervisors' => 'Supervisors',
        'evaluations' => 'Evaluations',
    ];

    // The columns of each report: key => the title in the table and in the file.
    private const COLUMNS = [
        'research' => [
            'code' => 'Code', 'title' => 'Title', 'type' => 'Type', 'category' => 'Category', 'status' => 'Status', 'year' => 'Year',
            'team' => 'Group/Team', 'batch' => 'Batch', 'members' => 'Students', 'supervisor' => 'Supervisor',
            'submitted' => 'Submitted',
        ],
        'teams' => [
            'name' => 'Group/Team', 'batch' => 'Batch', 'status' => 'Status', 'member_count' => 'Members', 'members' => 'Students',
            'research_code' => 'Research code', 'research' => 'Research', 'research_status' => 'Research status',
            'supervisor' => 'Supervisor', 'created' => 'Created',
        ],
        'students' => [
            'student_id' => 'Student ID', 'name' => 'Name', 'email' => 'Email', 'batch' => 'Batch', 'credits' => 'Credit hours',
            'team' => 'Group/Team', 'research_code' => 'Research code', 'research' => 'Research', 'research_status' => 'Research status',
            'supervisor' => 'Supervisor',
        ],
        'supervisors' => [
            'employee_id' => 'Employee ID', 'name' => 'Name', 'designation' => 'Designation', 'email' => 'Email',
            'projects' => 'Projects', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'evaluations' => 'Evaluations written',
            'research_management' => 'Research management',
        ],
        'evaluations' => [
            'date' => 'Date', 'code' => 'Research code', 'title' => 'Research', 'type' => 'Type', 'team' => 'Group/Team',
            'batch' => 'Batch', 'rating' => 'Rating (of 5)', 'remarks' => 'Remarks', 'evaluated_by' => 'Evaluated by',
        ],
    ];

    // The columns that are numbers (an Excel file keeps them as numbers).
    public const NUMBERS = ['year', 'evaluations', 'member_count', 'credits', 'projects', 'ongoing', 'completed', 'rating'];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // key => title for one report
    public function columns(string $type): array
    {
        return self::COLUMNS[$type];
    }

    // ---------------------------------------------------------------------------------
    // The rows
    // ---------------------------------------------------------------------------------

    /**
     * The rows of a report. $limit = 0 gives all of them (for a file). $f = the filters of the page:
     * q, type, status, category_id, batch_id, year (each report uses the ones that fit it).
     */
    public function rows(string $type, array $f, int $limit = 0, int $offset = 0): array
    {
        [$sql, $bind] = $this->query($type, $f);

        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    public function count(string $type, array $f): int
    {
        [$sql, $bind] = $this->query($type, $f);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ({$sql}) AS counted");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    // [the SELECT with its WHERE and ORDER BY, the values for the :names]
    private function query(string $type, array $f): array
    {
        return match ($type) {
            'research'    => $this->researchQuery($f),
            'teams'       => $this->teamsQuery($f),
            'students'    => $this->studentsQuery($f),
            'supervisors' => $this->supervisorsQuery($f),
            'evaluations' => $this->evaluationsQuery($f),
        };
    }

    private function researchQuery(array $f): array
    {
        $where = 'r.deleted_at IS NULL';
        $bind = [];

        if (in_array($f['type'] ?? '', ['Thesis', 'Research Paper', 'Project'], true)) {
            $where .= ' AND r.type = :type';
            $bind['type'] = $f['type'];
        }

        $status = $f['status'] ?? '';
        if ($status === 'waiting') {
            $where .= " AND r.status IN ('requested', 'pending')";
        } elseif (in_array($status, Research::STATUSES, true)) {
            $where .= ' AND r.status = :status';
            $bind['status'] = $status;
        }

        if ((int) ($f['category_id'] ?? 0) > 0) {
            $where .= ' AND r.category_id = :category_id';
            $bind['category_id'] = (int) $f['category_id'];
        }
        if ((int) ($f['batch_id'] ?? 0) > 0) {
            $where .= ' AND t.batch_id = :batch_id';
            $bind['batch_id'] = (int) $f['batch_id'];
        }
        if ((int) ($f['year'] ?? 0) > 0) {
            $where .= ' AND r.year = :year';
            $bind['year'] = (int) $f['year'];
        }
        if (($f['q'] ?? '') !== '') {
            $where .= ' AND (r.title LIKE :q1 OR r.code LIKE :q2)';
            $bind['q1'] = $bind['q2'] = '%' . $f['q'] . '%';
        }

        $sql = "SELECT r.id AS _id, r.code, r.title, r.type, c.name AS category, r.status, r.year,
                       t.name AS team, b.name AS batch, sv.name AS supervisor,
                       " . $this->studentList('research_student rs', 'rs.student_id', 'rs.research_id = r.id') . " AS members,
                       DATE(r.created_at) AS submitted
                FROM research r
                JOIN categories c ON c.id = r.category_id
                LEFT JOIN teams t ON t.id = r.team_id
                LEFT JOIN batches b ON b.id = t.batch_id
                LEFT JOIN users sv ON sv.id = r.supervisor_id
                WHERE {$where}
                ORDER BY r.id DESC";

        return [$sql, $bind];
    }

    private function teamsQuery(array $f): array
    {
        $where = 't.deleted_at IS NULL';
        $bind = [];

        if (in_array($f['status'] ?? '', ['requested', 'open', 'rejected', 'closed'], true)) {
            $where .= ' AND t.status = :status';
            $bind['status'] = $f['status'];
        }
        if ((int) ($f['batch_id'] ?? 0) > 0) {
            $where .= ' AND t.batch_id = :batch_id';
            $bind['batch_id'] = (int) $f['batch_id'];
        }
        if (($f['q'] ?? '') !== '') {
            $where .= ' AND t.name LIKE :q';
            $bind['q'] = '%' . $f['q'] . '%';
        }

        $sql = "SELECT t.id AS _id, t.name, b.name AS batch, t.status,
                       (SELECT COUNT(*) FROM team_members m WHERE m.team_id = t.id) AS member_count,
                       " . $this->studentList('team_members m', 'm.student_id', 'm.team_id = t.id') . " AS members,
                       r.code AS research_code, r.title AS research, r.status AS research_status, sv.name AS supervisor,
                       DATE(t.created_at) AS created
                FROM teams t
                JOIN batches b ON b.id = t.batch_id
                LEFT JOIN research r ON r.id = (SELECT r2.id FROM research r2 WHERE r2.team_id = t.id AND r2.deleted_at IS NULL ORDER BY r2.id DESC LIMIT 1)
                LEFT JOIN users sv ON sv.id = r.supervisor_id
                WHERE {$where}
                ORDER BY t.id ASC";

        return [$sql, $bind];
    }

    private function studentsQuery(array $f): array
    {
        $where = "u.role = 'student' AND u.deleted_at IS NULL";
        $bind = [];

        if ((int) ($f['batch_id'] ?? 0) > 0) {
            $where .= ' AND sp.batch_id = :batch_id';
            $bind['batch_id'] = (int) $f['batch_id'];
        }
        if (($f['q'] ?? '') !== '') {
            $where .= ' AND (u.name LIKE :q1 OR sp.student_id LIKE :q2 OR u.email LIKE :q3)';
            $bind['q1'] = $bind['q2'] = $bind['q3'] = '%' . $f['q'] . '%';
        }

        // the group/team and the research of a student: the newest one that is not rejected
        $sql = "SELECT u.id AS _id, sp.student_id, u.name, u.email, b.name AS batch, sp.completed_credit_hours AS credits,
                       (SELECT t.name FROM team_members tm JOIN teams t ON t.id = tm.team_id
                         WHERE tm.student_id = u.id AND t.deleted_at IS NULL AND t.status <> 'rejected' ORDER BY t.id DESC LIMIT 1) AS team,
                       r.code AS research_code, r.title AS research, r.status AS research_status, sv.name AS supervisor
                FROM users u
                JOIN student_profiles sp ON sp.user_id = u.id
                JOIN batches b ON b.id = sp.batch_id
                LEFT JOIN research r ON r.id = (SELECT r2.id FROM research_student rs JOIN research r2 ON r2.id = rs.research_id
                                                 WHERE rs.student_id = u.id AND r2.deleted_at IS NULL ORDER BY r2.id DESC LIMIT 1)
                LEFT JOIN users sv ON sv.id = r.supervisor_id
                WHERE {$where}
                ORDER BY CAST(sp.student_id AS UNSIGNED), sp.student_id";

        return [$sql, $bind];
    }

    private function supervisorsQuery(array $f): array
    {
        $where = "u.role = 'teacher' AND u.deleted_at IS NULL";
        $bind = [];

        if (($f['q'] ?? '') !== '') {
            $where .= ' AND (u.name LIKE :q1 OR tp.employee_id LIKE :q2)';
            $bind['q1'] = $bind['q2'] = '%' . $f['q'] . '%';
        }

        $count = "SELECT COUNT(*) FROM research r WHERE r.supervisor_id = u.id AND r.deleted_at IS NULL";

        $sql = "SELECT u.id AS _id, tp.employee_id, u.name, tp.designation, u.email,
                       ({$count} AND r.status IN ('ongoing', 'completed')) AS projects,
                       ({$count} AND r.status = 'ongoing') AS ongoing,
                       ({$count} AND r.status = 'completed') AS completed,
                       (SELECT COUNT(*) FROM evaluations e WHERE e.teacher_id = u.id) AS evaluations,
                       IF(tp.research_management = 1, 'Yes', 'No') AS research_management
                FROM users u
                JOIN teacher_profiles tp ON tp.user_id = u.id
                WHERE {$where}
                ORDER BY tp.employee_id";

        return [$sql, $bind];
    }

    private function evaluationsQuery(array $f): array
    {
        $where = 'r.deleted_at IS NULL';
        $bind = [];

        if (in_array($f['type'] ?? '', ['Thesis', 'Research Paper', 'Project'], true)) {
            $where .= ' AND r.type = :type';
            $bind['type'] = $f['type'];
        }
        if ((int) ($f['batch_id'] ?? 0) > 0) {
            $where .= ' AND t.batch_id = :batch_id';
            $bind['batch_id'] = (int) $f['batch_id'];
        }
        if (($f['q'] ?? '') !== '') {
            $where .= ' AND (r.title LIKE :q1 OR r.code LIKE :q2)';
            $bind['q1'] = $bind['q2'] = '%' . $f['q'] . '%';
        }

        $sql = "SELECT e.id AS _id, DATE(e.created_at) AS date, r.code, r.title, r.type, t.name AS team, b.name AS batch,
                       e.rating, e.remarks, ev.name AS evaluated_by
                FROM evaluations e
                JOIN research r ON r.id = e.research_id
                LEFT JOIN teams t ON t.id = r.team_id
                LEFT JOIN batches b ON b.id = t.batch_id
                LEFT JOIN users ev ON ev.id = e.teacher_id
                WHERE {$where}
                ORDER BY e.id DESC";

        return [$sql, $bind];
    }

    // "Name (ID), Name (ID)" of the students found by a link table. Only fixed text from this file is put into the SQL.
    private function studentList(string $from, string $studentColumn, string $on): string
    {
        return "(SELECT GROUP_CONCAT(CONCAT(u.name, ' (', COALESCE(sp.student_id, '-'), ')') ORDER BY u.name SEPARATOR ', ')
                 FROM {$from} JOIN users u ON u.id = {$studentColumn}
                 LEFT JOIN student_profiles sp ON sp.user_id = u.id
                 WHERE {$on})";
    }

    // ---------------------------------------------------------------------------------
    // The numbers at the top of the page
    // ---------------------------------------------------------------------------------

    public function summary(): array
    {
        $one = fn(string $sql) => (int) $this->db->query($sql)->fetchColumn();

        $byType = ['Project' => 0, 'Thesis' => 0, 'Research Paper' => 0];
        foreach ($this->db->query('SELECT type, COUNT(*) AS total FROM research WHERE deleted_at IS NULL GROUP BY type')->fetchAll() as $row) {
            $byType[$row['type']] = (int) $row['total'];
        }

        return [
            'students'    => $one("SELECT COUNT(*) FROM users WHERE role = 'student' AND deleted_at IS NULL"),
            'teachers'    => $one("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND deleted_at IS NULL"),
            'teams'       => $one("SELECT COUNT(*) FROM teams WHERE deleted_at IS NULL AND status IN ('open', 'closed')"),
            'research'    => $one('SELECT COUNT(*) FROM research WHERE deleted_at IS NULL'),
            'waiting'     => $one("SELECT COUNT(*) FROM research WHERE deleted_at IS NULL AND status IN ('requested', 'pending')"),
            'ongoing'     => $one("SELECT COUNT(*) FROM research WHERE deleted_at IS NULL AND status = 'ongoing'"),
            'completed'   => $one("SELECT COUNT(*) FROM research WHERE deleted_at IS NULL AND status = 'completed'"),
            'evaluations' => $one('SELECT COUNT(*) FROM evaluations'),
            'by_type'     => $byType,
        ];
    }

    // The years that have research (for the filter).
    public function years(): array
    {
        return array_map('intval', $this->db->query('SELECT DISTINCT year FROM research WHERE deleted_at IS NULL ORDER BY year DESC')->fetchAll(PDO::FETCH_COLUMN));
    }
}
