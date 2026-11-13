<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Research
{
    public const STATUSES = ['requested', 'pending', 'approved', 'ongoing', 'completed', 'rejected', 'correction', 'assigned'];

    // The statuses in which a group works with its supervisor on the project page. 'assigned' = the supervisor is set and
    // the topic comes later, 'requested' and 'correction' = the topic waits for the committee while the work goes on.
    public const WORKING = ['assigned', 'requested', 'correction', 'ongoing'];
    public const TYPES = ['Thesis', 'Research Paper', 'Project'];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ---------------------------------------------------------------------------------
    // Lists
    // ---------------------------------------------------------------------------------

    /**
     * One page of research, newest first. $f can contain:
     *   q            searches title, code, abstract, tags and student names
     *   statuses     a list of statuses to include, for example ['completed', 'ongoing']
     *   category_id, tag_ids (list of ids), type, year, supervisor_id
     *   created_by   only the research this user submitted
     *   mine         only the research this user submitted, supervises or works on
     * Deleted research is never returned.
     */
    public function paginate(array $f, int $limit, int $offset): array
    {
        [$where, $bind] = $this->filters($f);

        $stmt = $this->db->prepare(
            "SELECT r.id, r.code, r.title, r.abstract, r.type, r.category_id, r.year, r.status, r.target_status,
                    r.supervisor_id, r.team_id, r.created_by, r.review_note, r.reviewed_at, r.created_at,
                    c.name AS category_name, sv.name AS supervisor_name, cr.name AS creator_name, tt.name AS team_name,
                    tb.name AS batch_name
             FROM research r
             LEFT JOIN categories c ON c.id = r.category_id
             JOIN users cr ON cr.id = r.created_by
             LEFT JOIN users sv ON sv.id = r.supervisor_id
             LEFT JOIN teams tt ON tt.id = r.team_id
             LEFT JOIN batches tb ON tb.id = tt.batch_id
             WHERE {$where} ORDER BY " . $this->orderBy($f['sort'] ?? '') . " LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();

        return $this->withTagsAndStudents($rows);
    }

    // The sort orders of the lists (a name from the page => the ORDER BY). Anything else means the newest first.
    private const SORTS = [
        'newest'     => 'r.id DESC',
        'oldest'     => 'r.id ASC',
        'title'      => 'r.title ASC, r.id DESC',
        'title_desc' => 'r.title DESC, r.id DESC',
        'year_desc'  => 'r.year DESC, r.id DESC',
        'year_asc'   => 'r.year ASC, r.id DESC',
    ];

    private function orderBy(string $sort): string
    {
        return self::SORTS[$sort] ?? self::SORTS['newest'];
    }

    public function count(array $f): int
    {
        [$where, $bind] = $this->filters($f);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM research r WHERE {$where}");
        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    // Builds the WHERE part once, so paginate() and count() always agree.
    private function filters(array $f): array
    {
        $where = 'r.deleted_at IS NULL';
        $bind = [];

        $statuses = array_values(array_intersect($f['statuses'] ?? [], self::STATUSES));
        if ($statuses) {
            $marks = [];
            foreach ($statuses as $i => $status) {
                $marks[] = ':st' . $i;
                $bind['st' . $i] = $status;
            }
            $where .= ' AND r.status IN (' . implode(', ', $marks) . ')';
        }

        $search = trim((string) ($f['q'] ?? ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $where .= ' AND (r.title LIKE :q1 OR r.code LIKE :q2 OR r.abstract LIKE :q3
                OR EXISTS (SELECT 1 FROM research_tag rt JOIN tags t ON t.id = rt.tag_id
                           WHERE rt.research_id = r.id AND t.name LIKE :q4)
                OR EXISTS (SELECT 1 FROM research_student rs JOIN users su ON su.id = rs.student_id
                           WHERE rs.research_id = r.id AND su.name LIKE :q5))';
            $bind += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like];
        }

        if (!empty($f['category_id'])) {
            $where .= ' AND r.category_id = :category_id';
            $bind['category_id'] = (int) $f['category_id'];
        }

        // Several tags: a research is listed when it has at least one of them.
        if (!empty($f['tag_ids'])) {
            $marks = [];
            foreach (array_values($f['tag_ids']) as $i => $tagId) {
                $marks[] = ':tag' . $i;
                $bind['tag' . $i] = (int) $tagId;
            }
            $where .= ' AND EXISTS (SELECT 1 FROM research_tag rt2 WHERE rt2.research_id = r.id AND rt2.tag_id IN (' . implode(', ', $marks) . '))';
        }

        if (!empty($f['type']) && in_array($f['type'], self::TYPES, true)) {
            $where .= ' AND r.type = :type';
            $bind['type'] = $f['type'];
        }

        if (!empty($f['year'])) {
            $where .= ' AND r.year = :year';
            $bind['year'] = (int) $f['year'];
        }

        // The batch of the students (it also finds research that has no group/team).
        if (!empty($f['batch_id'])) {
            $where .= ' AND EXISTS (SELECT 1 FROM research_student rsb JOIN student_profiles spb ON spb.user_id = rsb.student_id
                                    WHERE rsb.research_id = r.id AND spb.batch_id = :batch_id)';
            $bind['batch_id'] = (int) $f['batch_id'];
        }

        // A student: the name, the student ID or the email.
        $student = trim((string) ($f['student'] ?? ''));
        if ($student !== '') {
            $where .= ' AND EXISTS (SELECT 1 FROM research_student rss
                                    JOIN users sus ON sus.id = rss.student_id
                                    LEFT JOIN student_profiles sps ON sps.user_id = sus.id
                                    WHERE rss.research_id = r.id AND (sus.name LIKE :student1 OR sus.email LIKE :student2 OR sps.student_id LIKE :student3))';
            $bind += ['student1' => '%' . $student . '%', 'student2' => '%' . $student . '%', 'student3' => '%' . $student . '%'];
        }

        // The departments of a committee member: only the research of a group of these departments.
        if (!empty($f['department_ids']) && is_array($f['department_ids'])) {
            $marks = [];
            foreach (array_values($f['department_ids']) as $i => $departmentId) {
                $marks[] = ':dep' . $i;
                $bind['dep' . $i] = (int) $departmentId;
            }
            $where .= ' AND EXISTS (SELECT 1 FROM teams td WHERE td.id = r.team_id AND td.department_id IN (' . implode(', ', $marks) . '))';
        }

        // Only the research that has a supervisor (the project pages).
        if (!empty($f['has_supervisor'])) {
            $where .= ' AND r.supervisor_id IS NOT NULL';
        }

        if (!empty($f['supervisor_id'])) {
            $where .= ' AND r.supervisor_id = :supervisor_id';
            $bind['supervisor_id'] = (int) $f['supervisor_id'];
        }

        if (!empty($f['created_by'])) {
            $where .= ' AND r.created_by = :created_by';
            $bind['created_by'] = (int) $f['created_by'];
        }

        if (!empty($f['mine'])) {
            $where .= ' AND (r.created_by = :mine1 OR r.supervisor_id = :mine3
                OR EXISTS (SELECT 1 FROM research_student rs2 WHERE rs2.research_id = r.id AND rs2.student_id = :mine2))';
            $bind['mine1'] = (int) $f['mine'];
            $bind['mine2'] = (int) $f['mine'];
            $bind['mine3'] = (int) $f['mine'];
        }

        return [$where, $bind];
    }

    // Adds 'tags' => [{id, name}] and 'students' => [{id, name, student_id}] to every row.
    private function withTagsAndStudents(array $rows): array
    {
        if (!$rows) {
            return [];
        }

        $ids = implode(',', array_map(fn($row) => (int) $row['id'], $rows));   // whole numbers only, safe to inline

        $tags = [];
        foreach ($this->db->query(
            "SELECT rt.research_id, t.id, t.name FROM research_tag rt JOIN tags t ON t.id = rt.tag_id
             WHERE rt.research_id IN ({$ids}) ORDER BY t.name"
        )->fetchAll() as $row) {
            $tags[$row['research_id']][] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }

        $students = [];
        foreach ($this->db->query(
            "SELECT rs.research_id, u.id, u.name, sp.student_id
             FROM research_student rs JOIN users u ON u.id = rs.student_id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE rs.research_id IN ({$ids}) ORDER BY u.name"
        )->fetchAll() as $row) {
            $students[$row['research_id']][] = ['id' => (int) $row['id'], 'name' => $row['name'], 'student_id' => $row['student_id']];
        }

        foreach ($rows as $i => $row) {
            $rows[$i]['tags'] = $tags[$row['id']] ?? [];
            $rows[$i]['students'] = $students[$row['id']] ?? [];
        }

        return $rows;
    }

    // ---------------------------------------------------------------------------------
    // One research
    // ---------------------------------------------------------------------------------

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, c.name AS category_name, sv.name AS supervisor_name, cr.name AS creator_name, tt.name AS team_name, tb.name AS batch_name
             FROM research r
             LEFT JOIN categories c ON c.id = r.category_id
             JOIN users cr ON cr.id = r.created_by
             LEFT JOIN users sv ON sv.id = r.supervisor_id
             LEFT JOIN teams tt ON tt.id = r.team_id
             LEFT JOIN batches tb ON tb.id = tt.batch_id
             WHERE r.id = :id AND r.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->withTagsAndStudents([$row])[0] : null;
    }

    /**
     * Adds a research with its tags and students, in one transaction, and gives it its code.
     * $data = title, abstract, type, category_id, year, status, target_status, supervisor_id, team_id, created_by
     */
    public function create(array $data, array $tagIds, array $studentIds): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO research (title, abstract, type, category_id, year, status, target_status, supervisor_id, team_id, created_by)
                 VALUES (:title, :abstract, :type, :category_id, :year, :status, :target_status, :supervisor_id, :team_id, :created_by)'
            );
            $stmt->execute($data);
            $id = (int) $this->db->lastInsertId();

            // RS-2026-0007: the year of the research and its id padded to 4 digits
            $code = 'RS-' . (int) $data['year'] . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
            $this->db->prepare('UPDATE research SET code = :code WHERE id = :id')->execute(['code' => $code, 'id' => $id]);

            $this->replaceLinks($id, $tagIds, $studentIds);
            $this->closeTeamIfCompleted($id, $data['status']);
            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Saves the details of a research. $studentIds = null keeps the current students as they are.
     * The status is not changed here: the admin approves or rejects with review().
     */
    public function update(int $id, array $data, array $tagIds, ?array $studentIds): void
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'UPDATE research SET title = :title, abstract = :abstract, type = :type, category_id = :category_id,
                    year = :year, supervisor_id = :supervisor_id WHERE id = :id AND deleted_at IS NULL'
            );
            $stmt->execute($data + ['id' => $id]);

            $this->replaceLinks($id, $tagIds, $studentIds);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Replaces the tags (and the students, unless $studentIds is null) with the given lists.
    private function replaceLinks(int $id, array $tagIds, ?array $studentIds): void
    {
        $this->db->prepare('DELETE FROM research_tag WHERE research_id = :id')->execute(['id' => $id]);
        $insert = $this->db->prepare('INSERT INTO research_tag (research_id, tag_id) VALUES (:research_id, :tag_id)');
        foreach (array_unique($tagIds) as $tagId) {
            $insert->execute(['research_id' => $id, 'tag_id' => (int) $tagId]);
        }

        if ($studentIds !== null) {
            $this->db->prepare('DELETE FROM research_student WHERE research_id = :id')->execute(['id' => $id]);
            $insert = $this->db->prepare('INSERT INTO research_student (research_id, student_id) VALUES (:research_id, :student_id)');
            foreach (array_unique($studentIds) as $studentId) {
                $insert->execute(['research_id' => $id, 'student_id' => (int) $studentId]);
            }
        }
    }

    // The department of the group of this research (or, without a group, of the student who asked). null = unknown.
    public function departmentId(array $item): ?int
    {
        if (!empty($item['team_id'])) {
            $stmt = $this->db->prepare('SELECT department_id FROM teams WHERE id = :id');
            $stmt->execute(['id' => (int) $item['team_id']]);
        } else {
            $stmt = $this->db->prepare('SELECT department_id FROM student_profiles WHERE user_id = :id');
            $stmt->execute(['id' => (int) $item['created_by']]);
        }

        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    /**
     * The group sends its topic: the details are saved and the research waits for the committee ('requested').
     * $data = title, abstract, type, category_id. The supervisor and the group stay as they are.
     */
    public function setTopic(int $id, array $data, array $tagIds, string $status): void
    {
        $this->db->beginTransaction();

        try {
            $this->db->prepare(
                'UPDATE research SET title = :title, abstract = :abstract, type = :type, category_id = :category_id, status = :status, review_note = NULL
                 WHERE id = :id AND deleted_at IS NULL'
            )->execute($data + ['status' => $status, 'id' => $id]);

            $this->replaceLinks($id, $tagIds, null);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // The committee rejected the topic of a group that already has its supervisor: the topic is cleared and the group
    // sends a new one. The note says why. (The rejected title stays in the history.)
    public function returnToAssigned(int $id, ?string $note): void
    {
        $this->db->beginTransaction();

        try {
            $this->db->prepare(
                "UPDATE research SET status = 'assigned', title = NULL, abstract = NULL, category_id = NULL, review_note = :note, reviewed_at = NOW()
                 WHERE id = :id AND deleted_at IS NULL"
            )->execute(['note' => $note ?: null, 'id' => $id]);

            $this->db->prepare('DELETE FROM research_tag WHERE research_id = :id')->execute(['id' => $id]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * The admin's decision on a requested, pending or approved research.
     * $status = approved, ongoing, completed or rejected.  $supervisorId can be null.  $note explains a rejection.
     */
    public function review(int $id, string $status, ?int $supervisorId, ?string $note): void
    {
        $stmt = $this->db->prepare(
            'UPDATE research SET status = :status, supervisor_id = :supervisor_id, review_note = :note, reviewed_at = NOW()
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['status' => $status, 'supervisor_id' => $supervisorId, 'note' => $note ?: null, 'id' => $id]);
        $this->closeTeamIfCompleted($id, $status);
    }

    // ---------------------------------------------------------------------------------
    // The students of a research
    // ---------------------------------------------------------------------------------

    public function isMember(int $researchId, int $studentId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM research_student WHERE research_id = :research_id AND student_id = :student_id');
        $stmt->execute(['research_id' => $researchId, 'student_id' => $studentId]);

        return (bool) $stmt->fetchColumn();
    }

    // A quick status change, for example ongoing -> completed.
    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE research SET status = :status WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['status' => $status, 'id' => $id]);
        $this->closeTeamIfCompleted($id, $status);
    }

    // A completed research frees its team: the team is closed and its members can join a new team.
    private function closeTeamIfCompleted(int $id, string $status): void
    {
        if ($status === 'completed') {
            $this->db->prepare("UPDATE teams SET status = 'closed' WHERE id = (SELECT team_id FROM research WHERE id = :id)")
                ->execute(['id' => $id]);
        }
    }

    // Soft delete: the row stays (with its tags and students) but disappears from every list.
    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE research SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------------------
    // Permanent delete (admin only)
    // ---------------------------------------------------------------------------------

    // A research even when it is already soft deleted (find() hides those).
    public function findAny(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, code, title, status, deleted_at FROM research WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // The trash: the research that were deleted (soft), the newest deletion first, with how many records each one has.
    public function trashed(): array
    {
        $rows = $this->db->query(
            "SELECT r.id, r.code, r.title, r.type, r.status, r.deleted_at, tt.name AS team_name, tb.name AS batch_name,
                    sv.name AS supervisor_name,
                    (SELECT COUNT(*) FROM milestones m WHERE m.research_id = r.id) AS milestones,
                    (SELECT COUNT(*) FROM research_comments c WHERE c.research_id = r.id) AS comments,
                    (SELECT COUNT(*) FROM evaluations e WHERE e.research_id = r.id) AS evaluations
             FROM research r
             LEFT JOIN teams tt ON tt.id = r.team_id
             LEFT JOIN batches tb ON tb.id = tt.batch_id
             LEFT JOIN users sv ON sv.id = r.supervisor_id
             WHERE r.deleted_at IS NOT NULL
             ORDER BY r.deleted_at DESC, r.id DESC"
        )->fetchAll();

        return $this->withTagsAndStudents($rows);
    }

    // Takes a research out of the trash: it is back in the library with all its records.
    public function restore(int $id): void
    {
        $this->db->prepare('UPDATE research SET deleted_at = NULL WHERE id = :id AND deleted_at IS NOT NULL')->execute(['id' => $id]);
    }

    // What a permanent delete would remove with this research: label => how many.
    public function impact(int $id): array
    {
        $count = function (string $sql) use ($id): int {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);

            return (int) $stmt->fetchColumn();
        };

        return [
            'milestones'  => $count('SELECT COUNT(*) FROM milestones WHERE research_id = :id'),
            'tasks'       => $count('SELECT COUNT(*) FROM tasks WHERE milestone_id IN (SELECT id FROM milestones WHERE research_id = :id)'),
            'comments'    => $count('SELECT COUNT(*) FROM research_comments WHERE research_id = :id'),
            'evaluations' => $count('SELECT COUNT(*) FROM evaluations WHERE research_id = :id'),
            'history'     => $count('SELECT COUNT(*) FROM research_activity WHERE research_id = :id'),
            'completion'  => $count('SELECT COUNT(*) FROM completion_requests WHERE research_id = :id'),
        ];
    }

    // Removes the research and everything that belongs to it, for ever. All or nothing (one transaction).
    // The group/team, the students and the teachers themselves are not touched.
    public function hardDelete(int $id): void
    {
        $this->db->beginTransaction();

        try {
            $run = function (string $sql) use ($id): void {
                $this->db->prepare($sql)->execute(['id' => $id]);
            };

            $run("DELETE FROM approvals WHERE subject = 'completion' AND subject_id IN (SELECT id FROM completion_requests WHERE research_id = :id)");
            $run("DELETE FROM approvals WHERE subject = 'topic' AND subject_id = :id");
            $run('DELETE FROM completion_confirmations WHERE request_id IN (SELECT id FROM completion_requests WHERE research_id = :id)');
            $run('DELETE FROM completion_requests WHERE research_id = :id');
            $run('DELETE FROM research_comments WHERE research_id = :id');   // before the milestones (a comment can belong to one)
            $run('DELETE FROM tasks WHERE milestone_id IN (SELECT id FROM milestones WHERE research_id = :id)');
            $run('DELETE FROM milestones WHERE research_id = :id');
            $run('DELETE FROM evaluations WHERE research_id = :id');
            $run('DELETE FROM research_activity WHERE research_id = :id');
            $run('DELETE FROM research_seen WHERE research_id = :id');
            $run('DELETE FROM research_student WHERE research_id = :id');
            $run('DELETE FROM research_tag WHERE research_id = :id');

            // The notifications that open this research: their link ends with view.php?id=5 or project.php?id=5.
            $this->db->prepare("DELETE FROM notifications WHERE link REGEXP :pattern")
                ->execute(['pattern' => '(view|project)\\.php\\?id=' . $id . '$']);

            $run('DELETE FROM research WHERE id = :id');

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ---------------------------------------------------------------------------------
    // Numbers for the library and the admin lists
    // ---------------------------------------------------------------------------------

    // How many research items there are in each status.
    public function statusCounts(): array
    {
        $counts = array_fill_keys(self::STATUSES, 0);

        foreach ($this->db->query(
            'SELECT status, COUNT(*) AS total FROM research WHERE deleted_at IS NULL GROUP BY status'
        )->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    // The same numbers for one user's own research (submitted, supervised or worked on).
    public function statusCountsFor(int $userId): array
    {
        $counts = array_fill_keys(self::STATUSES, 0);

        $stmt = $this->db->prepare(
            'SELECT r.status, COUNT(*) AS total FROM research r
             WHERE r.deleted_at IS NULL
               AND (r.created_by = :u1 OR r.supervisor_id = :u2
                    OR EXISTS (SELECT 1 FROM research_student rs WHERE rs.research_id = r.id AND rs.student_id = :u3))
             GROUP BY r.status'
        );
        $stmt->execute(['u1' => $userId, 'u2' => $userId, 'u3' => $userId]);

        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    // Research per category (only completed and ongoing, the ones the library shows).
    public function categoryCounts(): array
    {
        return $this->db->query(
            "SELECT c.id, c.name, COUNT(r.id) AS total
             FROM categories c
             JOIN research r ON r.category_id = c.id AND r.deleted_at IS NULL AND r.status IN ('completed', 'ongoing')
             WHERE c.deleted_at IS NULL
             GROUP BY c.id, c.name ORDER BY total DESC, c.name"
        )->fetchAll();
    }

    // The years that have research in the library, newest first (for the year filter).
    // $all = every research (the admin lists), not only the completed and ongoing ones.
    public function years(bool $all = false): array
    {
        $statuses = $all ? '' : " AND status IN ('completed', 'ongoing')";

        return array_map('intval', $this->db->query(
            "SELECT DISTINCT year FROM research WHERE deleted_at IS NULL{$statuses} ORDER BY year DESC"
        )->fetchAll(PDO::FETCH_COLUMN));
    }

    // Research using a category or tag, for the "cannot delete while in use" rule.
    public function countByCategory(int $categoryId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM research WHERE category_id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $categoryId]);

        return (int) $stmt->fetchColumn();
    }

    public function countByTag(int $tagId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM research_tag rt JOIN research r ON r.id = rt.research_id
             WHERE rt.tag_id = :id AND r.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $tagId]);

        return (int) $stmt->fetchColumn();
    }

    // The owner corrected a research the admin sent back (status correction) and sends it again: it goes back to the admin's queue.
    public function resubmit(int $id, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE research SET status = :status, review_note = NULL, reviewed_at = NULL WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
