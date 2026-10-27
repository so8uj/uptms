<?php

namespace App\Models;

use App\Config\Database;
use PDO;

// The numbers on the admin dashboard. Every count ignores soft deleted rows.
class Dashboard {

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // One number per card, in a plain array.
    public function adminNumbers(): array
    {
        $research = (new Research())->statusCounts();

        return [
            'students'      => $this->count("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 1 AND deleted_at IS NULL"),
            'teachers'      => $this->count("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND status = 1 AND deleted_at IS NULL"),
            'departments'   => $this->count('SELECT COUNT(*) FROM departments WHERE status = 1 AND deleted_at IS NULL'),
            'batches'       => $this->count('SELECT COUNT(*) FROM batches WHERE status = 1 AND deleted_at IS NULL'),

            'new_requests'    => $research['requested'] + $research['pending'],
            'need_supervisor' => $research['approved'],
            'ongoing'         => $research['ongoing'],
            'completed'       => $research['completed'],

            'milestones'    => $this->count('SELECT COUNT(*) FROM milestones WHERE deleted_at IS NULL'),
            'open_tasks'    => $this->count('SELECT COUNT(*) FROM tasks WHERE is_done = 0 AND deleted_at IS NULL'),
            'done_tasks'    => $this->count('SELECT COUNT(*) FROM tasks WHERE is_done = 1 AND deleted_at IS NULL'),
            'evaluations'   => $this->count('SELECT COUNT(*) FROM evaluations'),
        ];
    }

    private function count(string $sql): int
    {
        return (int) $this->db->query($sql)->fetchColumn();
    }
}
