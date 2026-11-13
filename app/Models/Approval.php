<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The signatures (table approvals) of the thesis committee on a topic or on a request to finish.
 * A few signatures decide (see ResearchController::tally).
 */
class Approval
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ---------------------------------------------------------------------------------
    // The signatures
    // ---------------------------------------------------------------------------------

    // The signatures of one subject: [{user_id, name, decision, note, updated_at}], oldest first.
    public function votes(string $subject, int $subjectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.user_id, u.name, a.decision, a.note, a.updated_at
             FROM approvals a JOIN users u ON u.id = a.user_id
             WHERE a.subject = :subject AND a.subject_id = :id ORDER BY a.updated_at, a.id"
        );
        $stmt->execute(['subject' => $subject, 'id' => $subjectId]);

        return array_map(function ($row) {
            $row['user_id'] = (int) $row['user_id'];

            return $row;
        }, $stmt->fetchAll());
    }

    // The signatures of several subjects at once: subject id => list of signatures.
    public function votesFor(string $subject, array $subjectIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $subjectIds))));
        if (!$ids) {
            return [];
        }

        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $stmt = $this->db->prepare(
            "SELECT a.subject_id, a.user_id, u.name, a.decision, a.note, a.updated_at
             FROM approvals a JOIN users u ON u.id = a.user_id
             WHERE a.subject = :subject AND a.subject_id IN ({$list}) ORDER BY a.updated_at, a.id"
        );
        $stmt->execute(['subject' => $subject]);

        $bySubject = [];
        foreach ($stmt->fetchAll() as $row) {
            $row['user_id'] = (int) $row['user_id'];
            $bySubject[(int) $row['subject_id']][] = $row;
        }

        return $bySubject;
    }

    // One person's signature (a second one replaces the first: a voter can change their mind until it is decided).
    public function vote(string $subject, int $subjectId, int $userId, string $decision, ?string $note): void
    {
        $this->db->prepare(
            'INSERT INTO approvals (subject, subject_id, user_id, decision, note) VALUES (:subject, :id, :user_id, :decision, :note)
             ON DUPLICATE KEY UPDATE decision = VALUES(decision), note = VALUES(note)'
        )->execute([
            'subject'  => $subject,
            'id'       => $subjectId,
            'user_id'  => $userId,
            'decision' => $decision,
            'note'     => $note !== null && $note !== '' ? mb_substr($note, 0, 500) : null,
        ]);
    }

    // The signatures are cleared when the group sends a new topic (a new round starts).
    public function clearVotes(string $subject, int $subjectId): void
    {
        $this->db->prepare('DELETE FROM approvals WHERE subject = :subject AND subject_id = :id')
            ->execute(['subject' => $subject, 'id' => $subjectId]);
    }

    // ---------------------------------------------------------------------------------
    // One decision at a time
    // ---------------------------------------------------------------------------------

    // Two signatures at the same moment must not both decide: a named lock around the counting.
    public function lock(string $name): bool
    {
        return (int) $this->db->query('SELECT GET_LOCK(' . $this->db->quote($name) . ', 5)')->fetchColumn() === 1;
    }

    public function unlock(string $name): void
    {
        $this->db->query('SELECT RELEASE_LOCK(' . $this->db->quote($name) . ')')->fetchColumn();
    }
}
