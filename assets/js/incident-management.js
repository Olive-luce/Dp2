document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('incidentTable');
    if (!table) return;

    const incidentTable = new DataTable('#incidentTable', {
        responsive: true,
        pageLength: 10,
        order: [[0, 'desc']]
    });

    const loadIncidents = function () {
        fetch('./../api/incidents_crud.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                const tbody = document.querySelector('#incidentTable tbody');
                if (!tbody) return;
                tbody.innerHTML = '';
                data.forEach(function (incident) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td>' + incident.title + '</td><td>' + incident.incident_type + '</td><td>' + incident.severity + '</td><td>' + incident.status + '</td><td>' + incident.priority + '</td><td>' + incident.reporter + '</td><td>' + incident.assigned_responder + '</td><td><button class="btn btn-sm btn-outline-primary edit-incident" data-id="' + incident.id + '">Edit</button> <button class="btn btn-sm btn-outline-success resolve-incident" data-id="' + incident.id + '">Resolve</button> <button class="btn btn-sm btn-outline-danger delete-incident" data-id="' + incident.id + '">Delete</button></td>';
                    tbody.appendChild(row);
                });
                incidentTable.clear();
                incidentTable.rows.add(Array.from(tbody.querySelectorAll('tr')));
                incidentTable.draw();
            });
    };

    loadIncidents();

    document.getElementById('incidentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const payload = {
            title: document.getElementById('incidentTitle').value,
            description: document.getElementById('incidentDescription').value,
            incident_type: document.getElementById('incidentType').value,
            severity: document.getElementById('severity').value,
            priority: document.getElementById('priority').value,
            status: document.getElementById('status').value,
            latitude: document.getElementById('latitude').value,
            longitude: document.getElementById('longitude').value,
            assigned_to: document.getElementById('assignedTo').value,
            reporter: document.getElementById('reporter').value,
            photo: document.getElementById('photo').value
        };
        fetch('./../api/incidents_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function () { loadIncidents(); document.getElementById('incidentForm').reset(); });
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('delete-incident')) {
            const id = event.target.getAttribute('data-id');
            fetch('./../api/incidents_crud.php?id=' + id, { method: 'DELETE' }).then(function () { loadIncidents(); });
        }
        if (event.target.classList.contains('resolve-incident')) {
            const id = event.target.getAttribute('data-id');
            fetch('./../api/incidents_crud.php', { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id, status: 'resolved' }) }).then(function () { loadIncidents(); });
        }
    });
});
