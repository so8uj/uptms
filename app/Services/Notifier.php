<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Research;
use App\Models\User;

/**
 * Makes the notifications. Two ways to use it:
 *
 *   Notifier::forActivity(...)   called by the research history (ResearchActivity::add): every line of the history
 *                                tells the people of that research. So no controller has to remember to notify.
 *   Notifier::toUsers(...)       a message for chosen people (used for the groups/teams).
 *
 * A notification never stops the action that caused it: every error is swallowed and written to the error log.
 */
class Notifier
{
    // action of the research history => [title, what happened]. {detail} is replaced by the detail of the history line.
    private const TEXTS = [
        'requested'            => ['New research request', 'requested the topic'],
        'submitted'            => ['New research submitted', 'submitted the research'],
        'added'                => ['Research added', 'added the research'],
        'resubmitted'          => ['Research sent again', 'improved the topic and sent it again'],
        'approved'             => ['Research approved', 'approved the topic'],
        'rejected'             => ['Research rejected', 'rejected the topic for good. {detail}'],
        'correction'           => ['Correction needed', 'sent the topic back for correction. {detail}'],
        'supervisor_assigned'  => ['Supervisor assigned', 'assigned the supervisor {detail}'],
        'member_added'         => ['Team member added', 'added {detail} to the team'],
        'member_removed'       => ['Team member removed', 'removed {detail} from the team'],
        'status_changed'       => ['Status changed', 'changed the status. {detail}'],
        'milestone_added'      => ['New milestone', 'added the milestone "{detail}"'],
        'milestone_removed'    => ['Milestone removed', 'deleted the milestone "{detail}"'],
        'task_added'           => ['New task', 'added the task "{detail}"'],
        'task_completed'       => ['Task completed', 'completed the task "{detail}"'],
        'task_reopened'        => ['Task opened again', 'opened the task "{detail}" again'],
        'task_removed'         => ['Task removed', 'deleted the task "{detail}"'],
        'commented'            => ['New comment', 'wrote a comment: {detail}'],
        'completion_requested' => ['Finish requested', 'asked to finish the research'],
        'completion_approved'  => ['Finish approved', 'approved the request to finish'],
        'completion_confirmed' => ['Finish confirmed', 'confirmed that the work is finished'],
        'completion_rejected'  => ['Finish rejected', 'rejected the request to finish. {detail}'],
        'completion_cancelled' => ['Finish cancelled', 'cancelled the request to finish'],
        'evaluated'            => ['New evaluation', 'evaluated the progress ({detail})'],
        'completed'            => ['Research completed', 'marked the research as completed'],
        'deleted'              => ['Research deleted', 'deleted the research'],
    ];

    // These also need a decision of the admins (and the research managers).
    private const FOR_ADMINS = ['requested', 'submitted', 'resubmitted', 'completion_confirmed'];

    /**
     * Tells the people of a research about one line of its history.
     * They are: the person who made it, the students of the team and the supervisor (not the person who did the action).
     */
    public static function forActivity(int $researchId, ?int $actorId, string $action, ?string $detail): void
    {
        if (!isset(self::TEXTS[$action])) {
            return;
        }

        try {
            $research = (new Research())->find($researchId);
            if (!$research) {
                return;
            }

            $notifications = new Notification();
            [$title, $what] = self::TEXTS[$action];

            $actor = $actorId ? (new User())->findActiveById($actorId) : null;
            $who = $actor['name'] ?? 'The administrator';
            $body = $who . ' ' . str_replace('{detail}', trim((string) $detail), $what);
            $body = trim(preg_replace('/\s+/', ' ', $body)) . ' - ' . $research['code'] . ' ' . $research['title'];

            // the people of this research
            $people = array_map(fn($student) => (int) $student['id'], $research['students']);
            $people[] = (int) $research['created_by'];
            if ($research['supervisor_id']) {
                $people[] = (int) $research['supervisor_id'];
            }

            $admins = in_array($action, self::FOR_ADMINS, true) ? $notifications->adminAudience() : [];

            $everyone = array_diff(array_unique(array_merge($people, $admins)), [(int) $actorId]);

            foreach ($notifications->activeUsers($everyone) as $user) {
                $link = in_array((int) $user['id'], $admins, true)
                    ? 'admin/research/view.php?id=' . $researchId
                    : self::researchLink($user['role'], $research);

                $notifications->add((int) $user['id'], 'research', $title, $body, $link);
            }
        } catch (\Throwable $e) {
            error_log('Notifier::forActivity: ' . $e->getMessage());
        }
    }

    // The page of a research for this kind of user.
    private static function researchLink(string $role, array $research): string
    {
        $id = (int) $research['id'];

        if ($role === 'admin') {
            return 'admin/research/view.php?id=' . $id;
        }

        // a project page exists only for ongoing and completed research; the details page opens the others (waiting, correction, rejected)
        $isProject = in_array($research['status'], ['ongoing', 'completed'], true);

        if ($role === 'teacher') {
            return $isProject ? 'teacher/project.php?id=' . $id : 'teacher/research/view.php?id=' . $id;
        }

        return $isProject ? 'student/project.php?id=' . $id : 'student/research/view.php?id=' . $id;
    }

    /**
     * A message for these users (not for $exceptId, the person who did the action).
     * $link is a page of the project, for example 'student/team.php'.
     */
    public static function toUsers(array $ids, string $type, string $title, string $body, ?string $link, ?int $exceptId = null): void
    {
        try {
            $notifications = new Notification();

            foreach ($notifications->activeUsers(array_diff($ids, [(int) $exceptId])) as $user) {
                $notifications->add((int) $user['id'], $type, $title, $body, $link);
            }
        } catch (\Throwable $e) {
            error_log('Notifier::toUsers: ' . $e->getMessage());
        }
    }

    // A message for the admins and the research managers.
    public static function toAdmins(string $type, string $title, string $body, ?string $link, ?int $exceptId = null): void
    {
        try {
            self::toUsers((new Notification())->adminAudience(), $type, $title, $body, $link, $exceptId);
        } catch (\Throwable $e) {
            error_log('Notifier::toAdmins: ' . $e->getMessage());
        }
    }
}
