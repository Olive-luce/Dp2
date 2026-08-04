document.addEventListener('DOMContentLoaded', function () {
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const announcementList = document.getElementById('announcementList');
    const communicationForm = document.getElementById('communicationForm');

    const renderNotifications = function (payload) {
        if (notificationBadge) {
            notificationBadge.textContent = payload.unread_count || 0;
            notificationBadge.style.display = (payload.unread_count > 0) ? 'inline-flex' : 'none';
        }
        if (notificationList) {
            notificationList.innerHTML = '';
            (payload.notifications || []).forEach(function (item) {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.innerHTML = '<div class="fw-semibold">' + item.notification_type + '</div><div class="small text-muted">' + item.message + '</div><div class="small text-muted">' + item.created_at + '</div>';
                notificationList.appendChild(li);
            });
        }
        if (announcementList) {
            announcementList.innerHTML = '';
            (payload.announcements || []).forEach(function (item) {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.innerHTML = '<div class="fw-semibold">' + item.title + '</div><div class="small text-muted">' + item.body + '</div>';
                announcementList.appendChild(li);
            });
        }
    };

    const loadData = function () {
        fetch('./../api/communications.php')
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                renderNotifications(payload);
            });
    };

    if (communicationForm) {
        communicationForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(communicationForm);
            const payload = {
                action: formData.get('action'),
                message: formData.get('message'),
                title: formData.get('title'),
                type: formData.get('type') || 'info',
                audience: formData.get('audience') || 'all'
            };
            fetch('./../api/communications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function () { loadData(); communicationForm.reset(); });
        });
    }

    loadData();
    setInterval(loadData, 15000);
});
