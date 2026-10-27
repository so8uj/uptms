<?php
/*
 * The Notifications page of every role. Put it inside a page whose Alpine component is Sobuj_notificationPage (notifications.js).
 */
?>

<div class="table-card-custom">

    <div class="table-header-control">
        <div class="d-flex gap-2">
            <button type="button" class="notif-tab" :class="filter === 'all' ? 'active' : ''" @click="setFilter('all')">All</button>
            <button type="button" class="notif-tab" :class="filter === 'unread' ? 'active' : ''" @click="setFilter('unread')">
                Unread <span x-show="unread > 0" x-text="'(' + unread + ')'"></span>
            </button>
        </div>

        <div class="table-filter-group">
            <button type="button" class="btn btn-outline-primary" @click="readAll()" :disabled="unread === 0">
                <i class="bi bi-check2-all me-1"></i> Mark all as read
            </button>
        </div>
    </div>

    <div class="text-center text-muted py-5" x-show="loading && items.length === 0">Loading...</div>
    <div class="text-center text-muted py-5" x-cloak x-show="!loading && items.length === 0"
        x-text="filter === 'unread' ? 'No unread notifications.' : 'No notifications yet.'"></div>

    <div class="notif-page-list">
        <template x-for="item in items" :key="item.id">
            <div class="notif-item" :class="item.is_read ? '' : 'unread'">
                <div class="notif-icon"><i class="bi" :class="icon(item)"></i></div>
                <div class="notif-text" @click="open(item)">
                    <div class="notif-title" x-text="item.title"></div>
                    <div class="notif-body" x-text="item.body"></div>
                    <div class="notif-time" x-text="ago(item)"></div>
                </div>
                <button type="button" class="btn btn-light btn-sm flex-shrink-0" @click="toggleRead(item)">
                    <i class="bi me-1" :class="item.is_read ? 'bi-envelope' : 'bi-envelope-open'"></i>
                    <span x-text="item.is_read ? 'Mark as unread' : 'Mark as read'"></span>
                </button>
            </div>
        </template>
    </div>

    <div class="table-footer-control">

        <span class="table-pagination-info">
            <template x-if="total > 0">
                <span><span x-text="total"></span> notifications</span>
            </template>
            <template x-if="total === 0">
                <span>0 notifications</span>
            </template>
        </span>

        <nav aria-label="Notification pagination" x-cloak x-show="pages > 1">
            <ul class="pagination mb-0">

                <li class="page-item" :class="page <= 1 ? 'disabled' : ''">
                    <a class="page-link" href="#" @click.prevent="goTo(page - 1)">Previous</a>
                </li>

                <template x-for="number in pages" :key="number">
                    <li class="page-item" :class="number === page ? 'active' : ''">
                        <a class="page-link" href="#" @click.prevent="goTo(number)" x-text="number"></a>
                    </li>
                </template>

                <li class="page-item" :class="page >= pages ? 'disabled' : ''">
                    <a class="page-link" href="#" @click.prevent="goTo(page + 1)">Next</a>
                </li>

            </ul>
        </nav>

    </div>
</div>
