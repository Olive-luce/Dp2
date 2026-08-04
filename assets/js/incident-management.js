document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('incidentTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const searchInput = document.getElementById('incidentSearch');
    const columnCount = table.querySelectorAll('thead th').length;
    const apiUrl = (document.body.dataset.baseUrl || '') + '/api/incidents_crud.php';
    let incidents = [];

    const escapeHtml = function (value) {
        return String(value === null || value === undefined || value === '' ? '—' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };

    const severityBadge = function (severity) {
        const level = String(severity || '').toLowerCase();
        const tone = level === 'critical' || level === 'high' ? 'danger' : (level === 'medium' ? 'warning' : 'success');
        return '<span class="badge badge-' + tone + '">' + escapeHtml(severity) + '</span>';
    };

    const statusBadge = function (status) {
        const state = String(status || '').toLowerCase();
        const tone = state === 'resolved' ? 'success' : (state === 'in_progress' ? 'primary' : 'warning');
        return '<span class="badge badge-' + tone + '">' + escapeHtml(String(status || '').replace(/_/g, ' ')) + '</span>';
    };

    const render = function (rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr><td class="table-empty" colspan="' + columnCount + '">No incidents match your search yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (incident) {
            return '<tr>'
                + '<td>' + escapeHtml(incident.title) + '</td>'
                + '<td>' + escapeHtml(incident.incident_type) + '</td>'
                + '<td>' + severityBadge(incident.severity) + '</td>'
                + '<td>' + statusBadge(incident.status) + '</td>'
                + '<td>' + escapeHtml(incident.reporter) + '</td>'
                + '<td>' + escapeHtml(incident.assigned_responder) + '</td>'
                + '<td><div class="table-actions">'
                + '<button class="btn btn-sm btn-outline-success resolve-incident" data-id="' + escapeHtml(incident.id) + '">Resolve</button>'
                + '<button class="btn btn-sm btn-outline-danger delete-incident" data-id="' + escapeHtml(incident.id) + '">Delete</button>'
                + '</div></td>'
                + '</tr>';
        }).join('');
    };

    const applyFilter = function () {
        const term = (searchInput ? searchInput.value : '').trim().toLowerCase();
        if (!term) {
            render(incidents);
            return;
        }
        render(incidents.filter(function (incident) {
            return Object.keys(incident).some(function (key) {
                return String(incident[key] || '').toLowerCase().indexOf(term) !== -1;
            });
        }));
    };

    const loadIncidents = function () {
        tbody.innerHTML = '<tr><td class="table-empty" colspan="' + columnCount + '"><span class="skeleton"></span></td></tr>';
        fetch(apiUrl)
            .then(function (response) { return response.json(); })
            .then(function (data) {
                incidents = Array.isArray(data) ? data : [];
                applyFilter();
            })
            .catch(function () {
                tbody.innerHTML = '<tr><td class="table-empty" colspan="' + columnCount + '">Unable to load incidents right now.</td></tr>';
            });
    };

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
    }

    loadIncidents();

    document.getElementById('incidentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = this;
        const payload = {
            title: document.getElementById('incidentTitle').value,
            description: document.getElementById('incidentDescription').value,
            incident_type: document.getElementById('incidentType').value,
            severity: document.getElementById('severity').value,
            priority: document.getElementById('priority').value,
            status: document.getElementById('status').value,
            latitude: form.querySelector('[data-picker-lat]').value,
            longitude: form.querySelector('[data-picker-lng]').value,
            assigned_to: document.getElementById('assignedTo').value,
            reporter: document.getElementById('reporter').value,
            address: form.querySelector('[data-picker-address]').value
        };
        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) { return response.json(); })
            .then(function (result) {
                if (result && result.success === false) {
                    if (window.showToast) { window.showToast(result.message || 'Could not save the incident.', 'error'); }
                    return;
                }
                if (window.showToast) { window.showToast('Incident saved.', 'success'); }
                form.reset();
                loadIncidents();
            });
    });

    document.addEventListener('click', function (event) {
        const target = event.target.closest('button');
        if (!target) return;
        const id = target.getAttribute('data-id');
        if (target.classList.contains('delete-incident')) {
            fetch(apiUrl + '?id=' + id, { method: 'DELETE' }).then(function () {
                if (window.showToast) { window.showToast('Incident deleted.', 'info'); }
                loadIncidents();
            });
        }
        if (target.classList.contains('resolve-incident')) {
            fetch(apiUrl, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: 'resolved' })
            }).then(function () {
                if (window.showToast) { window.showToast('Incident marked resolved.', 'success'); }
                loadIncidents();
            });
        }
    });
});
