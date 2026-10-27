<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The requests to finish a research (tables completion_requests and completion_confirmations).
 * Status: teacher -> team -> admin -> done, or rejected / cancelled on the way.
 */
class CompletionRequest {

    // The statuses where the request is still alive (a research can have only one of them at a time).
    public const ACTIVE = ['teacher', 'team', 'admin'];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM completion_requests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    // The newest request of a research (any status), with the name of the student who asked.
    public function latest(int $researchId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.name AS requested_by_name FROM completion_requests c
             JOIN users u ON u.id = c.requested_by
             WHERE c.research_id = :research_id ORDER BY c.id DESC LIMIT 1'
        );
        $stmt->execute(['research_id' => $researchId]);

        return $stmt->fetch() ?: null;
    }

    // Is there a request that is still alive for this research?
    public function hasActive(int $researchId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM completion_requests WHERE research_id = :research_id AND status IN ('teacher', 'team', 'admin') LIMIT 1");
        $stmt->execute(['research_id' => $researchId]);

        return (bool) $stmt->fetchColumn();
    }

    // A new request. The student who asks has confirmed already.
    public function create(int $researchId, int $studentId): int
    {
        $stmt = $this->db->prepare('INSERT INTO completion_requests (research_id, requested_by) VALUES (:research_id, :requested_by)');
        $stmt->execute(['research_id' => $researchId, 'requested_by' => $studentId]);
        $id = (int) $this->db->lastInsertId();

        $this->confirm($id, $studentId);

        return $id;
    }

    public function setStatus(int $id, string $status, ?int $decidedBy = null, ?string $note = null): void
    {
        $stmt = $this->db->prepare('UPDATE completion_requests SET status = :status, decided_by = :decided_by, note = :note WHERE id = :id');
        $stmt->execute(['status' => $status, 'decided_by' => $decidedBy, 'note' => $note ?: null, 'id' => $id]);
    }

    public function confirm(int $id, int $studentId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO completion_confirmations (request_id, student_id) VALUES (:request_id, :student_id)');
        $stmt->execute(['request_id' => $id, 'student_id' => $studentId]);
    }

    // The ids of the students who confirmed.
    public function confirmedIds(int $id): array
    {
        $stmt = $this->db->prepare('SELECT student_id FROM completion_confirmations WHERE request_id = :id');
        $stmt->execute(['id' => $id]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // The status of the open request of each research: [research_id => 'teacher' | 'team' | 'admin']
    public function activeStatuses(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $result = [];
        foreach ($this->db->query(
            "SELECT research_id, status FROM completion_requests WHERE research_id IN ({$list}) AND status IN ('teacher', 'team', 'admin')"
        )->fetchAll() as $row) {
            $result[(int) $row['research_id']] = $row['status'];
        }

        return $result;
    }

    // The requests that wait for the administrator, with the research (oldest first).
    public function waitingForAdmin(): array
    {
        return $this->db->query(
            "SELECT c.id, c.research_id, c.created_at, r.code, r.title, u.name AS requested_by_name
             FROM completion_requests c
             JOIN research r ON r.id = c.research_id AND r.deleted_at IS NULL
             JOIN users u ON u.id = c.requested_by
             WHERE c.status = 'admin' ORDER BY c.id"
        )->fetchAll();
    }

    // How many requests wait for this supervisor's decision.
    public function countForTeacher(int $teacherId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM completion_requests c JOIN research r ON r.id = c.research_id AND r.deleted_at IS NULL
             WHERE c.status = 'teacher' AND r.supervisor_id = :teacher_id"
        );
        $stmt->execute(['teacher_id' => $teacherId]);

        return (int) $stmt->fetchColumn();
    }

    // How many requests wait for this student's confirmation.
    public function countForStudent(int $studentId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM completion_requests c
             JOIN research_student rs ON rs.research_id = c.research_id AND rs.student_id = :student_id
             JOIN research r ON r.id = c.research_id AND r.deleted_at IS NULL
             WHERE c.status = 'team'
               AND NOT EXISTS (SELECT 1 FROM completion_confirmations cc WHERE cc.request_id = c.id AND cc.student_id = :student_id2)"
        );
        $stmt->execute(['student_id' => $studentId, 'student_id2' => $studentId]);

        return (int) $stmt->fetchColumn();
    }

    // A research was completed by the administrator in another way: close its open request.
    public function closeActive(int $researchId, int $decidedBy): void
    {
        $stmt = $this->db->prepare(
            "UPDATE completion_requests SET status = 'done', decided_by = :decided_by
             WHERE research_id = :research_id AND status IN ('teacher', 'team', 'admin')"
        );
        $stmt->execute(['decided_by' => $decidedBy, 'research_id' => $researchId]);
    }
}
