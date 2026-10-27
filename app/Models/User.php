<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class User
{
    public const ROLES = ['admin', 'teacher', 'student'];

    // Teachers and students keep extra data in a profile table. Admins have none.
    // The column names here are fixed in the code, so they are safe to use inside SQL.
    private const PROFILES = [
        'teacher' => [
            'table'  => 'teacher_profiles',
            'fields' => ['employee_id', 'department_id', 'designation', 'specialization', 'research_area', 'research_management'],
        ],
        'student' => [
            'table'  => 'student_profiles',
            'fields' => ['student_id', 'department_id', 'batch_id', 'gender', 'cgpa', 'completed_credit_hours'],
        ],
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findByEmail(string $email): ?array
    {
        // lock_seconds = how many seconds the account is still locked (0 or less = not locked).
        // MySQL calculates it with its own clock, so PHP and MySQL time zones cannot disagree.
        $stmt = $this->db->prepare(
            'SELECT *, COALESCE(TIMESTAMPDIFF(SECOND, NOW(), locked_until), 0) AS lock_seconds
             FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    // The user for the navbar and the session check: only if the account is active and not deleted.
    public function findActiveById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, role, photo FROM users WHERE id = :id AND status = 1 AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // Adds 1 to the failed attempts. When it reaches $max, lock the account for $minutes and start counting again.
    public function recordFailedAttempt(int $id, int $max, int $minutes): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET
                locked_until    = IF(failed_attempts + 1 >= :max1, DATE_ADD(NOW(), INTERVAL :minutes MINUTE), locked_until),
                failed_attempts = IF(failed_attempts + 1 >= :max2, 0, failed_attempts + 1)
             WHERE id = :id'
        );
        $stmt->execute(['max1' => $max, 'max2' => $max, 'minutes' => $minutes, 'id' => $id]);
    }

    // After a good login the counter starts from zero again.
    public function clearFailedAttempts(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function create(string $name, string $email, string $password, string $role): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // "My Account": the phone number, and the photo only when a new one is given.
    public function updateProfile(int $id, string $name, ?string $phone): void
    {
        $this->db->prepare('UPDATE users SET name = :name, phone = :phone WHERE id = :id AND deleted_at IS NULL')
            ->execute(['name' => $name, 'phone' => $phone, 'id' => $id]);
    }

    // The profile picture: the path inside the uploads folder.
    public function updatePhoto(int $id, string $path): void
    {
        $this->db->prepare('UPDATE users SET photo = :photo WHERE id = :id AND deleted_at IS NULL')
            ->execute(['photo' => $path, 'id' => $id]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------------
    // Used by the admin screens (admins, teachers, students)
    // ---------------------------------------------------------------------------------

    // The columns, joins and search columns each role needs. Admins are only in the users table.
    private function parts(string $role): array
    {
        $columns = 'u.id, u.name, u.email, u.phone, u.photo, u.status, u.last_login_at';
        $joins = '';
        $search = ['u.name', 'u.email'];

        if ($role === 'teacher') {
            $columns .= ', tp.employee_id, tp.department_id, tp.designation, tp.specialization, tp.research_area,
                          tp.research_management, d.name AS department_name';
            $joins = 'LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                      LEFT JOIN departments d ON d.id = tp.department_id';
            $search[] = 'tp.employee_id';
        }

        if ($role === 'student') {
            $columns .= ', sp.student_id, sp.department_id, sp.batch_id, sp.gender, sp.cgpa, sp.completed_credit_hours,
                          d.name AS department_name, b.name AS batch_name, b.shift AS batch_shift,
                          cs.name AS batch_session_name';
            $joins = 'LEFT JOIN student_profiles sp ON sp.user_id = u.id
                      LEFT JOIN departments d ON d.id = sp.department_id
                      LEFT JOIN batches b ON b.id = sp.batch_id
                      LEFT JOIN academic_sessions cs ON cs.id = b.admission_session_id';
            $search[] = 'sp.student_id';
        }

        return [$columns, $joins, $search];
    }

    // The columns a list may be sorted by: the name the page sends => the SQL. Anything else is ignored.
    private const SORTS = [
        'admin'   => ['name' => 'u.name', 'email' => 'u.email', 'last_login' => 'u.last_login_at', 'status' => 'u.status'],
        'teacher' => [
            'name' => 'u.name', 'status' => 'u.status', 'employee' => 'tp.employee_id', 'department' => 'd.name',
            'designation' => "FIELD(tp.designation, 'Professor', 'Associate Professor', 'Assistant Professor', 'Senior Lecturer', 'Lecturer')",
        ],
        'student' => [
            'name' => 'u.name', 'status' => 'u.status', 'roll' => 'CAST(sp.student_id AS UNSIGNED)', 'batch' => 'b.name',
            'credits' => 'sp.completed_credit_hours', 'cgpa' => 'sp.cgpa', 'department' => 'd.name',
        ],
    ];

    // "ORDER BY ..." for the sort the page asked for; newest first when there is none.
    private function orderBy(string $role, array $options): string
    {
        $column = self::SORTS[$role][$options['sort'] ?? ''] ?? null;
        if ($column === null) {
            return 'u.id DESC';
        }

        return $column . (($options['dir'] ?? '') === 'desc' ? ' DESC' : ' ASC') . ', u.id DESC';
    }

    private function filters(string $role, string $search, string $status, array $searchColumns, array $options = []): array
    {
        $where = 'u.role = :role AND u.deleted_at IS NULL';
        $bind = ['role' => $role];

        if ($search !== '') {
            $like = [];
            foreach ($searchColumns as $number => $column) {
                $like[] = "{$column} LIKE :search{$number}";
                $bind["search{$number}"] = '%' . $search . '%';
            }
            $where .= ' AND (' . implode(' OR ', $like) . ')';
        }

        if ($status === '0' || $status === '1') {
            $where .= ' AND u.status = :status';
            $bind['status'] = $status;
        }

        // only the students of one batch
        if ($role === 'student' && (int) ($options['batch_id'] ?? 0) > 0) {
            $where .= ' AND sp.batch_id = :batch_id';
            $bind['batch_id'] = (int) $options['batch_id'];
        }

        return [$where, $bind];
    }

    /**
     * One page of users of one role. Deleted users are never returned.
     * $options: batch_id (students only), sort (a key of SORTS), dir (asc | desc). Without a sort: newest first.
     */
    public function paginate(string $role, string $search, string $status, int $limit, int $offset, array $options = []): array
    {
        [$columns, $joins, $searchColumns] = $this->parts($role);
        [$where, $bind] = $this->filters($role, $search, $status, $searchColumns, $options);
        $order = $this->orderBy($role, $options);

        $stmt = $this->db->prepare(
            "SELECT {$columns} FROM users u {$joins} WHERE {$where} ORDER BY {$order} LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);

        return $stmt->fetchAll();
    }

    public function count(string $role, string $search, string $status, array $options = []): int
    {
        [, $joins, $searchColumns] = $this->parts($role);
        [$where, $bind] = $this->filters($role, $search, $status, $searchColumns, $options);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users u {$joins} WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    // One user of that role, with the profile data. Null when not found or deleted.
    public function findByRole(string $role, int $id): ?array
    {
        [$columns, $joins] = $this->parts($role);

        $stmt = $this->db->prepare(
            "SELECT {$columns} FROM users u {$joins} WHERE u.id = :id AND u.role = :role AND u.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'role' => $role]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Adds a user and (for teachers and students) the profile, in one transaction:
     * both rows are saved, or neither.
     * $account = name, email, phone, status, password, photo     $profile = the role's own fields
     */
    public function createAccount(string $role, array $account, array $profile): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (name, email, phone, photo, password, role, status)
                 VALUES (:name, :email, :phone, :photo, :password, :role, :status)'
            );
            $stmt->execute([
                'name'     => $account['name'],
                'email'    => $account['email'],
                'phone'    => $account['phone'] ?: null,
                'photo'    => $account['photo'] ?? null,
                'password' => password_hash($account['password'], PASSWORD_DEFAULT),
                'role'     => $role,
                'status'   => $account['status'],
            ]);
            $id = (int) $this->db->lastInsertId();

            if (isset(self::PROFILES[$role])) {
                $fields = self::PROFILES[$role]['fields'];
                $columns = implode(', ', $fields);
                $marks = implode(', ', array_map(fn($field) => ':' . $field, $fields));

                $stmt = $this->db->prepare(
                    'INSERT INTO ' . self::PROFILES[$role]['table'] . " (user_id, {$columns}) VALUES (:user_id, {$marks})"
                );
                $stmt->execute(['user_id' => $id] + $this->profileValues($role, $profile));
            }

            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Saves changes to a user and the profile, in one transaction.
     * A new password is only set when $account['password'] is not empty, and a photo only when it is given.
     */
    public function updateAccount(string $role, int $id, array $account, array $profile): void
    {
        $this->db->beginTransaction();

        try {
            $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone, status = :status';
            $bind = [
                'name'   => $account['name'],
                'email'  => $account['email'],
                'phone'  => $account['phone'] ?: null,
                'status' => $account['status'],
                'id'     => $id,
            ];

            if (!empty($account['password'])) {
                $sql .= ', password = :password';
                $bind['password'] = password_hash($account['password'], PASSWORD_DEFAULT);
            }
            if (!empty($account['photo'])) {
                $sql .= ', photo = :photo';
                $bind['photo'] = $account['photo'];
            }

            $stmt = $this->db->prepare($sql . ' WHERE id = :id AND deleted_at IS NULL');
            $stmt->execute($bind);

            if (isset(self::PROFILES[$role])) {
                // Insert or update: a user created before profiles existed has no profile row yet,
                // and gets one the first time they are saved here.
                $fields = self::PROFILES[$role]['fields'];
                $columns = implode(', ', $fields);
                $marks = implode(', ', array_map(fn($field) => ':' . $field, $fields));
                $sets = implode(', ', array_map(fn($field) => "{$field} = VALUES({$field})", $fields));

                $stmt = $this->db->prepare(
                    'INSERT INTO ' . self::PROFILES[$role]['table'] . " (user_id, {$columns}) VALUES (:user_id, {$marks})
                     ON DUPLICATE KEY UPDATE {$sets}"
                );
                $stmt->execute(['user_id' => $id] + $this->profileValues($role, $profile));
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Takes only the fields this role's profile has; an empty value is saved as NULL when the column allows it.
    private function profileValues(string $role, array $profile): array
    {
        $required = ['employee_id', 'department_id', 'designation', 'student_id', 'batch_id'];
        $values = [];

        foreach (self::PROFILES[$role]['fields'] as $field) {
            $value = $profile[$field] ?? null;
            $values[$field] = ($value === '' && !in_array($field, $required, true)) ? null : $value;
        }

        return $values;
    }

    // Soft delete: the user keeps their row (and their history) but disappears from lists and cannot log in.
    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    // How many admins are active and not deleted. The last one can never be removed.
    public function activeAdminCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 1 AND deleted_at IS NULL"
        )->fetchColumn();
    }

    // Active teachers, for the supervisor dropdown.
    public function teacherOptions(): array
    {
        return $this->db->query(
            "SELECT u.id, u.name, tp.employee_id FROM users u
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             WHERE u.role = 'teacher' AND u.status = 1 AND u.deleted_at IS NULL ORDER BY u.name"
        )->fetchAll();
    }

    // How many of these ids are active students. Used to reject a tampered list.
    public function countActiveStudents(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return 0;
        }

        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE id IN ({$list}) AND role = 'student' AND status = 1 AND deleted_at IS NULL"
        )->fetchColumn();
    }

    // Is this user an active teacher who has the research management permission (given by the admin)?
    public function hasResearchManagement(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT tp.research_management FROM teacher_profiles tp JOIN users u ON u.id = tp.user_id
             WHERE tp.user_id = :id AND u.role = 'teacher' AND u.status = 1 AND u.deleted_at IS NULL"
        );
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() === 1;
    }
}
