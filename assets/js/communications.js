document.addEventListener('DOMContentLoaded', function () {
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const announcementList = document.getElementById('announcementList');
    const communicationForm = document.getElementById('communicationForm');
    const feedback = document.getElementById('communicationFeedback');
    const markAllRead = document.getElementById('markAllRead');
    const actionSelect = document.getElementById('commAction');
    const titleGroup = document.getElementById('commTitleGroup');
    const titleInput = document.getElementById('commTitle');

    if (!notificationList && !announcementList && !communicationForm) return;

    const apiUrl = (document.body.dataset.baseUrl || '') + '/api/communications.php';
    const isAdmin = document.body.dataset.role === 'admin';

    const escapeHtml = function (value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    };

    const showFeedback = function (message, isError) {
        if (!feedback) return;
        feedback.classList.remove('d-none', 'alert-danger', 'alert-success');
        feedback.classList.add(isError ? 'alert-danger' : 'alert-success');
        feedback.textContent = message;
    };

    const render = function (payload) {
        if (notificationBadge) {
            const unread = payload.unread_count || 0;
            notificationBadge.textContent = unread;
            notificationBadge.style.display = unread > 0 ? 'inline-flex' : 'none';
        }

        if (notificationList) {
            const notifications = payload.notifications || [];
            notificationList.innerHTML = notifications.length
                ? notifications.map(function (item) {
                    return '<li class="list-group-item d-flex justify-content-between align-items-start gap-3">'
                        + '<div>'
                        + '<div class="fw-semibold">' + escapeHtml(item.notification_type) + '</div>'
                        + '<div class="small">' + escapeHtml(item.message) + '</div>'
                        + '<div class="small text-muted">' + escapeHtml(item.created_at) + '</div>'
                        + '</div>'
                        + (item.status === 'unread'
                            ? '<button class="btn btn-sm btn-outline-primary mark-read" data-id="' + escapeHtml(item.id) + '">Mark read</button>'
                            : '<span class="badge bg-secondary">read</span>')
                        + '</li>';
                }).join('')
                : '<li class="list-group-item text-muted">No notifications yet.</li>';
        }

        if (announcementList) {
            const announcements = payload.announcements || [];
            announcementList.innerHTML = announcements.length
                ? announcements.map(function (item) {
                    return '<li class="list-group-item d-flex justify-content-between align-items-start gap-3">'
                        + '<div>'
                        + '<div class="fw-semibold">' + escapeHtml(item.title) + '</div>'
                        + '<div class="small">' + escapeHtml(item.body) + '</div>'
                        + '<div class="small text-muted">' + escapeHtml(item.published_by || 'Operations') + ' &middot; ' + escapeHtml(item.created_at) + '</div>'
                        + '</div>'
                        + (isAdmin ? '<button class="btn btn-sm btn-outline-danger archive-announcement" data-id="' + escapeHtml(item.id) + '">Archive</button>' : '')
                        + '</li>';
                }).join('')
                : '<li class="list-group-item text-muted">No announcements published yet.</li>';
        }
    };

    const loadData = function () {
        fetch(apiUrl, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(render)
            .catch(function () { /* transient poll failure */ });
    };

    const post = function (payload) {
        return fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) { return response.json(); });
    };

    if (actionSelect && titleGroup) {
        const syncTitleField = function () {
            const needsTitle = actionSelect.value === 'announcement';
            titleGroup.classList.toggle('d-none', !needsTitle);
            if (titleInput) titleInput.required = needsTitle;
        };
        actionSelect.addEventListener('change', syncTitleField);
        syncTitleField();
    }

    if (communicationForm) {
        communicationForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(communicationForm);

            post({
                action: formData.get('action'),
                title: formData.get('title'),
                message: formData.get('message'),
                type: formData.get('type') || 'info',
                audience: formData.get('audience') || 'all'
            }).then(function (result) {
                showFeedback(result.message || (result.success ? 'Sent.' : 'Unable to send.'), !result.success);
                if (result.success) {
                    communicationForm.reset();
                    if (actionSelect) actionSelect.dispatchEvent(new Event('change'));
                    loadData();
                }
            });
        });
    }

    if (markAllRead) {
        markAllRead.addEventListener('click', function () {
            post({ action: 'mark_read' }).then(loadData);
        });
    }

    document.addEventListener('click', function (event) {
        const target = event.target;

        if (target.classList.contains('mark-read')) {
            post({ action: 'mark_read', id: target.dataset.id }).then(loadData);
        }

        if (target.classList.contains('archive-announcement')) {
            post({ action: 'delete_announcement', id: target.dataset.id }).then(function (result) {
                showFeedback(result.message || 'Announcement archived.', !result.success);
                loadData();
            });
        }
    });

    loadData();
    setInterval(loadData, 15000);
});
