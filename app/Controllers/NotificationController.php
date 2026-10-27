<?php

namespace App\Controllers;

use App\Models\Notification;

/**
 * Called by api/notifications.php (every logged-in user). Everything works on the logged-in user only:
 * there is no user id in the request, so nobody can read or change another person's notifications.
 */
class NotificationController {

    private const PER_PAGE = 10;

    // One page of my notifications. filter = 'unread' shows only the unread ones. perPage is used by the small list of the bell.
    public static function index($actor, $query){

        $model  = new Notification();
        $unread = ($query['filter'] ?? '') === 'unread';
        $per    = min(30, max(1, (int) ($query['per_page'] ?? self::PER_PAGE)));
        $page   = max(1, (int) ($query['page'] ?? 1));

        $total = $model->count((int) $actor['id'], $unread);
        $pages = max(1, (int) ceil($total / $per));
        $page  = min($page, $pages);

        return [
            'items'   => $model->paginate((int) $actor['id'], $unread, $per, ($page - 1) * $per),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => $per,
            'unread'  => $model->count((int) $actor['id'], true),
        ];
    }

    // Only the number of unread notifications (the badge of the bell asks for it again and again).
    public static function unread($actor){

        return ['unread' => (new Notification())->count((int) $actor['id'], true)];
    }

    // op = read | unread (one notification, with id) or read_all
    public static function change($actor, $request){

        $model = new Notification();

        switch ($request['op'] ?? '') {
            case 'read':
                $model->setRead((int) ($request['id'] ?? 0), (int) $actor['id'], true);
                break;
            case 'unread':
                $model->setRead((int) ($request['id'] ?? 0), (int) $actor['id'], false);
                break;
            case 'read_all':
                $model->markAllRead((int) $actor['id']);
                break;
            default:
                return ['success' => false, 'message' => 'Unknown action.'];
        }

        return ['success' => true, 'unread' => $model->count((int) $actor['id'], true)];
    }
}
