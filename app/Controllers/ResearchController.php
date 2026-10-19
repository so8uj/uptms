<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Research;
use App\Models\Tag;
use App\Models\User;
use App\Services\Validation;

/**
 * One controller for research, used by four API files:
 *   api/research.php        admin: the whole catalog, add / edit / review / delete
 *   api/library.php         everyone logged in: the read-only library
 *   api/my_research.php     student and teacher: their own submissions
 *   api/research_options.php  the dropdown lists for the forms
 *
 * It only works with data and returns arrays (no HTML, no redirects).
 * $actor is the logged in user: ['id' => .., 'role' => ..., 'name' => ...] from Auth::currentUser().
 *
 * Who may do what:
 *   admin    everything. Research the admin adds is approved straight away (ongoing or completed).
 *   student  asks for a topic: status "requested", and the admin decides.
 *   teacher  adds a research only when the admin gave the permission: status "pending", and the admin decides.
 * Everything a student or teacher submits waits for the admin, because the thesis committee decides what stays.
 */
class ResearchController {

    private const PER_PAGE = 10;
    private const MAX_TAGS = 10;

    // The statuses an owner may still edit or withdraw (nothing was approved yet).
    private const OPEN = ['requested', 'pending', 'rejected'];

    // ---------------------------------------------------------------------------------
    // Lists
    // ---------------------------------------------------------------------------------

    // Admin: the whole catalog. status = '' (all), one status, or 'review' (requested + pending).
    public static function adminIndex($query){

        $status = $query['status'] ?? '';
        $statuses = match (true) {
            $status === 'review'                    => ['requested', 'pending'],
            in_array($status, Research::STATUSES, true) => [$status],
            default                                 => [],
        };

        $result = self::page(self::filtersFrom($query) + ['statuses' => $statuses], $query);
        $result['counts'] = (new Research())->statusCounts();

        return $result;
    }

    /**
     * The library: only completed research, plus ongoing research when include_ongoing is on.
     * Requested, pending and rejected research is never shown here, whoever asks.
     */
    public static function libraryIndex($query){

        $includeOngoing = ($query['include_ongoing'] ?? '') === '1';
        $statuses = $includeOngoing ? ['completed', 'ongoing'] : ['completed'];

        $model = new Research();
        $result = self::page(self::filtersFrom($query) + ['statuses' => $statuses], $query);

        // Only what a reader needs: nothing about who submitted it or why it was rejected.
        $result['research'] = array_map(fn($row) => array_diff_key($row, array_flip(
            ['review_note', 'reviewed_at', 'target_status', 'created_by', 'creator_name']
        )), $result['research']);

        $counts = $model->statusCounts();
        $result['counts'] = ['completed' => $counts['completed'], 'ongoing' => $counts['ongoing']];
        $result['categories'] = $model->categoryCounts();
        $result['years'] = $model->years();

        return $result;
    }

    // A student's or teacher's own research (submitted, supervised or worked on), with every status.
    public static function mine($actor, $query){

        $status = $query['status'] ?? '';
        $statuses = in_array($status, Research::STATUSES, true) ? [$status] : [];

        $result = self::page(self::filtersFrom($query) + ['statuses' => $statuses, 'mine' => $actor['id']], $query);

        // Students can always ask for a topic. A teacher may add research only with the admin's permission.
        $result['can_add'] = $actor['role'] === 'student' || (new User())->canAddResearch((int) $actor['id']);

        return $result;
    }

    // The search and filters shared by the lists.
    private static function filtersFrom($query): array
    {
        return [
            'q'           => trim($query['q'] ?? ''),
            'category_id' => (int) ($query['category_id'] ?? 0),
            'tag_id'      => (int) ($query['tag_id'] ?? 0),
            'type'        => $query['type'] ?? '',
            'year'        => (int) ($query['year'] ?? 0),
        ];
    }

    private static function page(array $filters, $query): array
    {
        $model = new Research();
        $total = $model->count($filters);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min(max(1, (int) ($query['page'] ?? 1)), $pages);

        return [
            'research' => $model->paginate($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'perPage'  => self::PER_PAGE,
        ];
    }

    // ---------------------------------------------------------------------------------
    // One research
    // ---------------------------------------------------------------------------------

    // For the edit form. Admin can open any; others only their own research that can still be edited.
    public static function find($actor, $id){

        $item = (new Research())->find((int) $id);

        if (!$item) {
            return null;
        }

        if ($actor['role'] !== 'admin' && !self::ownerMayChange($actor, $item)) {
            return null;
        }

        return $item;
    }

    private static function ownerMayChange($actor, $item): bool
    {
        return (int) $item['created_by'] === (int) $actor['id'] && in_array($item['status'], self::OPEN, true);
    }

    // The dropdown lists for the forms. Only the admin also gets the teachers (for the supervisor).
    public static function options($actor){

        $options = [
            'categories' => (new Category())->options(),
            'tags'       => (new Tag())->options(),
            'types'      => Research::TYPES,
        ];

        if ($actor['role'] === 'admin') {
            $options['teachers'] = (new User())->teacherOptions();
        }

        return $options;
    }

    // Admin only: finds students by name or student id for the student picker.
    public static function lookupStudents($query){
        $search = trim($query['q'] ?? '');

        return ['students' => $search === '' ? [] : (new User())->searchStudents($search)];
    }

    // ---------------------------------------------------------------------------------
    // Save (add or edit)
    // ---------------------------------------------------------------------------------

    /**
     * $id is null when adding and the research id when editing.
     * Returns ['success' => bool, 'errors' => [field => [messages]], 'message' => text for the toast]
     */
    public static function save($actor, $request, $id = null){

        $role  = $actor['role'];
        $model = new Research();
        $users = new User();
        $current = null;

        // ---------- who may do this ----------

        if ($role === 'teacher' && !$users->canAddResearch((int) $actor['id'])) {
            return self::fail('You do not have permission to add research. Please ask the administrator.');
        }

        if ($id !== null) {
            $current = $model->find((int) $id);

            if (!$current || ($role !== 'admin' && !self::ownerMayChange($actor, $current))) {
                return self::fail('This research cannot be changed.');
            }
        }

        // ---------- the rules ----------

        $rules = [
            'title'       => 'required|max:255',
            'abstract'    => 'required|min:20|max:5000',
            'type'        => 'required|in:' . implode(',', Research::TYPES),
            'category_id' => 'required|integer',
        ];

        if ($role !== 'student') {
            $rules['year'] = 'required|integer|between:1990,2100';
        }
        if ($role === 'admin' && $id === null) {
            $rules['status'] = 'required|in:ongoing,completed';
        }
        if ($role === 'admin' && $id !== null) {
            $rules['status'] = 'in:ongoing,completed';
        }
        if ($role === 'teacher') {
            $rules['target_status'] = 'required|in:ongoing,completed';
        }

        $validate = Validation::validate($request, $rules, [
            'category_id'   => 'Category',
            'target_status' => 'Progress',
        ]);
        $errors = $validate['errors'];

        // ---------- the lists and the dropdowns (they can be tampered with, so check them) ----------

        if (empty($errors['category_id']) && !(new Category())->isActive((int) $request['category_id'])) {
            $errors['category_id'][] = 'Please choose a valid category.';
        }

        $tagIds = self::ids($request['tags'] ?? []);
        if (count($tagIds) > self::MAX_TAGS) {
            $errors['tags'][] = 'Please choose at most ' . self::MAX_TAGS . ' tags.';
        } elseif ($tagIds && (new Tag())->countActive($tagIds) !== count($tagIds)) {
            $errors['tags'][] = 'Please choose valid tags.';
        }

        $supervisorId = null;
        $studentIds = null;   // null = keep the current students

        if ($role === 'admin') {
            $supervisorId = self::idOrNull($request['supervisor_id'] ?? null);
            if ($supervisorId !== null && !self::isActiveTeacher($supervisorId)) {
                $errors['supervisor_id'][] = 'Please choose a valid supervisor.';
            }

            $studentIds = self::ids($request['students'] ?? []);
            if ($studentIds && $users->countActiveStudents($studentIds) !== count($studentIds)) {
                $errors['students'][] = 'Please choose valid students.';
            }

            // A research that is still waiting for review changes status only through review().
            if ($current && isset($request['status']) && !in_array($current['status'], ['ongoing', 'completed'], true)
                && $request['status'] !== $current['status']) {
                $errors['status'][] = 'Approve or reject this research on the Pending page first.';
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        // ---------- save ----------

        $data = [
            'title'         => trim($request['title']),
            'abstract'      => trim($request['abstract']),
            'type'          => $request['type'],
            'category_id'   => (int) $request['category_id'],
            'year'          => $role === 'student' ? (int) ($current['year'] ?? date('Y')) : (int) $request['year'],
            'supervisor_id' => $supervisorId,
        ];

        if ($id === null) {
            [$status, $target, $supervisor, $students] = match ($role) {
                'admin'   => [$request['status'], $request['status'], $supervisorId, $studentIds],
                'teacher' => ['pending', $request['target_status'], (int) $actor['id'], []],
                default   => ['requested', 'ongoing', null, [(int) $actor['id']]],   // a student's request
            };

            // array_merge (not +) so these values replace the ones already in $data, such as supervisor_id.
            $model->create(array_merge($data, [
                'supervisor_id' => $supervisor,
                'status'        => $status,
                'target_status' => $target,
                'created_by'    => (int) $actor['id'],
            ]), $tagIds, $students);

            $message = $role === 'admin' ? 'Research added.' : 'Your research was sent to the administrator for approval.';

            return ['success' => true, 'errors' => [], 'message' => $message];
        }

        // Editing: the owner cannot change who supervises or which students work on it.
        if ($role !== 'admin') {
            $data['supervisor_id'] = $current['supervisor_id'] !== null ? (int) $current['supervisor_id'] : null;
        }

        $model->update((int) $id, $data, $tagIds, $studentIds);

        if ($role === 'admin' && isset($request['status']) && in_array($current['status'], ['ongoing', 'completed'], true)) {
            $model->setStatus((int) $id, $request['status']);
        }

        // A rejected research that the owner improved goes back to the administrator.
        if ($role !== 'admin' && $current['status'] === 'rejected') {
            $model->resubmit((int) $id, $role === 'teacher' ? 'pending' : 'requested');
        }

        return ['success' => true, 'errors' => [], 'message' => 'Research updated.'];
    }

    // ---------------------------------------------------------------------------------
    // Admin decisions
    // ---------------------------------------------------------------------------------

    /**
     * Approve or reject a requested / pending research.
     * $request = id, decision (approve | reject), status (ongoing | completed, for approve),
     *            supervisor_id (needed when it becomes ongoing), note (needed for reject)
     */
    public static function review($request){

        $model = new Research();
        $item  = $model->find((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }
        if (!in_array($item['status'], ['requested', 'pending'], true)) {
            return self::fail('This research was already reviewed.');
        }

        $decision = $request['decision'] ?? '';
        $errors = [];

        if ($decision === 'reject') {
            $note = trim($request['note'] ?? '');
            if ($note === '') {
                $errors['note'][] = 'Please write the reason for rejecting.';
            } elseif (mb_strlen($note) > 500) {
                $errors['note'][] = 'The reason must not be more than 500 characters.';
            }

            if ($errors) {
                return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
            }

            $model->review((int) $item['id'], 'rejected', $item['supervisor_id'] !== null ? (int) $item['supervisor_id'] : null, $note);

            return ['success' => true, 'errors' => [], 'message' => 'Research rejected.'];
        }

        if ($decision !== 'approve') {
            return self::fail('Unknown decision.');
        }

        $status = $request['status'] ?? $item['target_status'];
        if (!in_array($status, ['ongoing', 'completed'], true)) {
            $errors['status'][] = 'Please choose Ongoing or Completed.';
        }

        $supervisorId = self::idOrNull($request['supervisor_id'] ?? null);
        if ($supervisorId === null && $status === 'ongoing') {
            $errors['supervisor_id'][] = 'Please choose a supervisor for an ongoing research.';
        } elseif ($supervisorId !== null && !self::isActiveTeacher($supervisorId)) {
            $errors['supervisor_id'][] = 'Please choose a valid supervisor.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        $model->review((int) $item['id'], $status, $supervisorId, trim($request['note'] ?? '') ?: null);

        return ['success' => true, 'errors' => [], 'message' => 'Research approved.'];
    }

    // Ongoing -> completed (the research is finished and joins the library as completed).
    public static function complete($request){

        $model = new Research();
        $item  = $model->find((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }
        if ($item['status'] !== 'ongoing') {
            return self::fail('Only an ongoing research can be marked as completed.');
        }

        $model->setStatus((int) $item['id'], 'completed');

        return ['success' => true, 'message' => 'Marked as completed.'];
    }

    // Admin deletes any research. An owner can only withdraw their own, not yet approved, research.
    public static function delete($actor, $request){

        $model = new Research();
        $item  = $model->find((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }

        if ($actor['role'] !== 'admin' && !self::ownerMayChange($actor, $item)) {
            return self::fail('This research cannot be deleted.');
        }

        $model->softDelete((int) $item['id']);

        return ['success' => true, 'message' => $actor['role'] === 'admin' ? 'Research deleted.' : 'Your request was withdrawn.'];
    }

    // ---------------------------------------------------------------------------------
    // Small helpers
    // ---------------------------------------------------------------------------------

    private static function fail(string $message): array
    {
        return ['success' => false, 'errors' => [], 'message' => $message];
    }

    // A list of whole numbers from the request (anything else is thrown away).
    private static function ids($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, fn($v) => is_scalar($v) && ctype_digit((string) $v)))));
    }

    private static function idOrNull($value): ?int
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function isActiveTeacher(int $id): bool
    {
        $teacher = (new User())->findByRole('teacher', $id);

        return $teacher !== null && (int) $teacher['status'] === 1;
    }

}
