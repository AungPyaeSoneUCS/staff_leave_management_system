function showAlertModal(message) {
    const modal = document.getElementById('alert-modal');
    const messageEl = document.getElementById('alert-modal-message');

    if (!modal || !messageEl) {
        alert(message);
        return;
    }

    messageEl.textContent = message;
    modal.classList.remove('hidden');
    modal.querySelector('button').focus();
}

function closeAlertModal() {
    const modal = document.getElementById('alert-modal');
    if (!modal) return;
    modal.classList.add('hidden');
}

window.showAlertModal = showAlertModal;
window.closeAlertModal = closeAlertModal;

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('alert-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeAlertModal();
        }
    }
});

let confirmModalResolve = null;

function showConfirmModal(message) {
    return new Promise(function(resolve) {
        confirmModalResolve = resolve;
        const modal = document.getElementById('confirm-modal');
        const messageEl = document.getElementById('confirm-modal-message');
        const confirmBtn = document.getElementById('confirm-modal-confirm-btn');

        if (!modal || !messageEl || !confirmBtn) {
            resolve(window.confirm(message));
            return;
        }

        messageEl.textContent = message;
        modal.classList.remove('hidden');
        confirmBtn.focus();
    });
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    const resolve = confirmModalResolve;
    confirmModalResolve = null;
    if (resolve) resolve(false);
}

function confirmConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    const resolve = confirmModalResolve;
    confirmModalResolve = null;
    if (resolve) resolve(true);
}

window.showConfirmModal = showConfirmModal;
window.closeConfirmModal = closeConfirmModal;
window.confirmConfirmModal = confirmConfirmModal;

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('confirm-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeConfirmModal();
        }
    }
});

document.querySelectorAll('form[data-confirm]').forEach(function(form) {
    function onSubmit(e) {
        e.preventDefault();
        const message = form.getAttribute('data-confirm') || __('common.confirm');
        showConfirmModal(message).then(function(confirmed) {
            if (confirmed) {
                form.removeEventListener('submit', onSubmit);
                form.submit();
            }
        });
    }
    form._confirmHandler = onSubmit;
    form.addEventListener('submit', onSubmit);
});

document.querySelectorAll('a[data-confirm], button[data-confirm]').forEach(function(el) {
    function onClick(e) {
        const message = el.getAttribute('data-confirm');
        if (!message) return;
        e.preventDefault();
        const href = el.getAttribute('href');
        const form = el.closest('form');
        showConfirmModal(message).then(function(confirmed) {
            if (confirmed) {
                if (form && form._confirmHandler) {
                    form.removeEventListener('submit', form._confirmHandler);
                }
                if (form) {
                    form.submit();
                } else if (href) {
                    window.location.href = href;
                }
            }
        });
    }
    el._confirmClickHandler = onClick;
    el.addEventListener('click', onClick);
});

let notificationDropdownOpen = false;

function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notification-dropdown');
    const isOpen = !dropdown.classList.contains('hidden');

    if (isOpen) {
        dropdown.classList.add('hidden');
        notificationDropdownOpen = false;
    } else {
        dropdown.classList.remove('hidden');
        notificationDropdownOpen = true;
        loadNotifications();
    }
}

function loadNotifications() {
    fetch('/notifications/data')
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('notification-list');
            const badge = document.getElementById('notification-badge');
            const markAllBtn = document.getElementById('mark-all-read-btn');
            const viewAll = document.getElementById('notification-view-all');
            const loading = document.getElementById('notification-loading');

            if (loading) {
                loading.remove();
            }

            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('hidden');
                badge.classList.add('notification-badge-pulse');
            } else {
                badge.classList.add('hidden');
            }

            if (markAllBtn) {
                markAllBtn.style.display = data.unread_count > 0 ? 'inline' : 'none';
            }

            if (viewAll) {
                viewAll.style.display = 'block';
            }

            if (data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="px-4 py-8 text-center">
                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-sm text-slate-500">No Notifications</p>
                    </div>
                `;
                return;
            }

            let html = '<ul class="divide-y divide-slate-100">';
            data.notifications.forEach(function(notification) {
                const title = notification.data.title || 'Notification';
                const message = notification.data.message || '';
                const time = new Date(notification.created_at).toLocaleString();
                const unread = notification.read_at === null;
                let url = notification.data.url || '#';

                if (url && url.includes('localhost:8000')) {
                    url = url.replace('http://localhost:8000', window.location.origin);
                }

                html += `
                    <li class="px-4 py-3 hover:bg-slate-50/50 transition-colors cursor-pointer" onclick="handleNotificationClick('${notification.id}', '${url}')">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-50 text-primary-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">${escapeHtml(title)}</p>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">${escapeHtml(message)}</p>
                                <p class="text-xs text-slate-400 mt-1">${time}</p>
                            </div>
                            ${unread ? '<span class="flex-shrink-0 h-2 w-2 rounded-full bg-primary-500 mt-2"></span>' : ''}
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
            list.innerHTML = html;
        })
        .catch(function(error) {
            const loading = document.getElementById('notification-loading');
            if (loading) {
                loading.innerHTML = '<p class="text-xs text-slate-400 text-center px-4 py-8">Failed to load notifications</p>';
            }
        });
}

function handleNotificationClick(id, url) {
    if (url && url.includes('localhost:8000')) {
        url = url.replace('http://localhost:8000', window.location.origin);
    }

    fetch('/notifications/' + id + '/read', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        }
    }).then(function() {
        loadNotifications();
        updateBadgeCount();
        if (url && url !== '#') {
            setTimeout(function() {
                window.location.href = url;
            }, 100);
        }
    });
}

function markAllNotificationsRead() {
    fetch('/notifications/read-all', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        }
    }).then(function() {
        loadNotifications();
        updateBadgeCount();
    });
}

window.toggleNotificationDropdown = toggleNotificationDropdown;
window.loadNotifications = loadNotifications;
window.handleNotificationClick = handleNotificationClick;
window.markAllNotificationsRead = markAllNotificationsRead;
window.updateBadgeCount = updateBadgeCount;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notification-dropdown');
    const bell = document.getElementById('notification-bell');
    const wrapper = document.getElementById('notification-wrapper');

    if (dropdown && !wrapper?.contains(event.target)) {
        dropdown.classList.add('hidden');
        notificationDropdownOpen = false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notification-bell');
    if (bell) {
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotificationDropdown();
        });
    }

    updateBadgeCount();
});

function updateBadgeCount() {
    fetch('/notifications/data')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            const markAllBtn = document.getElementById('mark-all-read-btn');

            if (badge && data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('hidden');
                badge.classList.add('notification-badge-pulse');
            } else if (badge) {
                badge.classList.add('hidden');
            }

            if (markAllBtn) {
                markAllBtn.style.display = data.unread_count > 0 ? 'inline' : 'none';
            }
        })
        .catch(() => {});
}
