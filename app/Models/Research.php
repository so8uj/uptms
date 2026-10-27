<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Research
{
    public const STATUSES = ['requested', 'pending', 'approved', 'ongoing', 'completed', 'rejected', 'correction'];
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
             JOIN categories c ON c.id = r.category_id
             JOIN users cr ON cr.id = r.created_by
             LEFT JOIN users sv ON sv.id = r.supervisor_id
             LEFT JOIN teams tt ON tt.id = r.team_id
             LEFT JOIN batches tb ON tb.id = tt.batch_id
             WHERE {$where} ORDER BY r.id DESC LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();

        return $this->withTagsAndStudents($rows);
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
             JOIN categories c ON c.id = r.category_id
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
    public function years(): array
    {
        return array_map('intval', $this->db->query(
            "SELECT DISTINCT year FROM research WHERE deleted_at IS NULL AND status IN ('completed', 'ongoing') ORDER BY year DESC"
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
