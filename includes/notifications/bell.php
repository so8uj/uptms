<?php
/*
 * The notification bell of the top bar (admin, teacher and student).
 * Its Alpine component Sobuj_notificationBell is in js/notifications.js, which the footer loads.
 * $myRole comes from dashboard_header.php: it decides which Notifications page "See all" opens.
 */
?>

<div class="dropdown ms-2" @notifications-changed.window="loadCount()" x-data="Sobuj_notificationBell('<?= url('api/notifications.php') ?>', '<?= url() ?>')">
    <button class="navbar-action-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
        aria-label="Notifications" @click="loadList()">
        <i class="bi bi-bell"></i>
        <span class="notif-badge" x-cloak x-show="unread > 0" x-text="unread > 99 ? '99+' : unread"></span>
    </button>

    <div class="dropdown-menu dropdown-menu-end notif-menu p-0">

        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong>Notifications</strong>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="readAll()" x-show="unread > 0">
                Mark all as read
            </button>
        </div>

        <div class="d-flex gap-2 px-3 py-2 border-bottom">
            <button type="button" class="notif-tab" :class="filter === 'all' ? 'active' : ''" @click="setFilter('all')">All</button>
            <button type="button" class="notif-tab" :class="filter === 'unread' ? 'active' : ''" @click="setFilter('unread')">
                Unread <span x-show="unread > 0" x-text="'(' + unread + ')'"></span>
            </button>
        </div>

        <div class="notif-list">
            <div class="text-center text-muted small py-4" x-show="loading && items.length === 0">Loading...</div>
            <div class="text-center text-muted small py-4" x-cloak x-show="!loading && items.length === 0"
                x-text="filter === 'unread' ? 'No unread notifications.' : 'No notifications yet.'"></div>

            <template x-for="item in items" :key="item.id">
                <div class="notif-item" :class="item.is_read ? '' : 'unread'">
                    <div class="notif-icon"><i class="bi" :class="icon(item)"></i></div>
                    <div class="notif-text" @click="open(item)">
                        <div class="notif-title" x-text="item.title"></div>
                        <div class="notif-body" x-text="item.body"></div>
                        <div class="notif-time" x-text="ago(item)"></div>
                    </div>
                    <button type="button" class="notif-toggle" @click.stop="toggleRead(item)"
                        :title="item.is_read ? 'Mark as unread' : 'Mark as read'">
                        <i class="bi" :class="item.is_read ? 'bi-envelope' : 'bi-envelope-open'"></i>
                    </button>
                </div>
            </template>
        </div>

        <a href="<?= url($myRole . '/notifications.php') ?>" class="d-block text-center py-2 border-top text-decoration-none">
            See all notifications
        </a>
    </div>
</div>
