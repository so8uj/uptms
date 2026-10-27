<?php

namespace App\Models;

use App\Config\Database;
use PDO;

// The supervisor's progress evaluations of a research (table evaluations). Saved once, never changed.
class Evaluation {

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Newest first, with the teacher's name.
    public function forResearch(int $researchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.id, e.rating, e.remarks, e.created_at, u.name AS teacher_name
             FROM evaluations e JOIN users u ON u.id = e.teacher_id
             WHERE e.research_id = :research_id ORDER BY e.id DESC'
        );
        $stmt->execute(['research_id' => $researchId]);

        return $stmt->fetchAll();
    }

    // The evaluations of several research for a list page, newest first, with the research.
    public function forResearchIds(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        return $this->db->query(
            "SELECT e.id, e.research_id, e.rating, e.remarks, e.created_at, u.name AS teacher_name,
                    r.code AS research_code, r.title AS research_title
             FROM evaluations e
             JOIN users u ON u.id = e.teacher_id
             JOIN research r ON r.id = e.research_id
             WHERE e.research_id IN ({$list}) ORDER BY e.id DESC"
        )->fetchAll();
    }

    public function create(int $researchId, int $teacherId, int $rating, string $remarks): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO evaluations (research_id, teacher_id, rating, remarks) VALUES (:research_id, :teacher_id, :rating, :remarks)'
        );
        $stmt->execute(['research_id' => $researchId, 'teacher_id' => $teacherId, 'rating' => $rating, 'remarks' => $remarks]);

        return (int) $this->db->lastInsertId();
    }

    // The newest rating of each research: [research_id => rating]
    public function latestRatings(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $result = [];
        foreach ($this->db->query(
            "SELECT research_id, rating FROM evaluations
             WHERE id IN (SELECT MAX(id) FROM evaluations WHERE research_id IN ({$list}) GROUP BY research_id)"
        )->fetchAll() as $row) {
            $result[(int) $row['research_id']] = (int) $row['rating'];
        }

        return $result;
    }

    // How many evaluations each research has: [research_id => n]
    public function countsFor(array $researchIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $researchIds)));
        if (!$ids) {
            return [];
        }
        $list = implode(',', $ids);   // whole numbers only, safe to inline

        $result = [];
        foreach ($this->db->query("SELECT research_id, COUNT(*) AS total FROM evaluations WHERE research_id IN ({$list}) GROUP BY research_id")->fetchAll() as $row) {
            $result[(int) $row['research_id']] = (int) $row['total'];
        }

        return $result;
    }
}
