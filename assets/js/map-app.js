document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    const map = L.map('map', { zoomControl: true }).setView([23.6850, 90.3563], 6);
    const baseLayers = {
        Street: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }),
        Satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri' })
    };

    baseLayers.Street.addTo(map);
    L.control.layers(baseLayers).addTo(map);

    const incidentColors = {
        Flood: '#0d6efd',
        Fire: '#dc3545',
        Earthquake: '#6f42c1',
        Cyclone: '#20c997',
        'Road Block': '#fd7e14',
        'Medical Emergency': '#198754',
        'Infrastructure Damage': '#ffc107'
    };

    const markerIcon = function (incidentType) {
        return L.divIcon({
            html: '<i class="fa-solid fa-location-dot" style="color:' + (incidentColors[incidentType] || '#0d6efd') + '; font-size: 22px;"></i>',
            className: 'custom-marker',
            iconSize: [24, 24]
        });
    };

    const renderPins = function () {
        fetch('./api/map_incidents.php')
            .then(function (response) { return response.json(); })
            .then(function (pins) {
                pins.forEach(function (pin) {
                    const marker = L.marker([pin.latitude, pin.longitude], { icon: markerIcon(pin.incident_type) }).addTo(map);
                    const role = document.body.dataset.role || 'citizen';
                    const canModify = role === 'admin' || role === 'responder';
                    const popup = '<strong>' + pin.incident_type + '</strong><br>' + pin.description + '<br><small>Severity: ' + pin.severity + '<br>Reporter: ' + pin.reporter + '<br>Date: ' + pin.created_at + '</small>';
                    marker.bindPopup(popup + (canModify ? '<br><button class="btn btn-sm btn-outline-danger mt-2 delete-pin" data-id="' + pin.id + '">Delete</button> <button class="btn btn-sm btn-outline-success mt-2 resolve-pin" data-id="' + pin.id + '">Resolve</button>' : ''));
                });
            });
    };

    renderPins();

    const form = document.getElementById('incidentForm');
    const feedback = document.getElementById('mapFeedback');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    map.on('click', function (e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        latitudeInput.value = lat;
        longitudeInput.value = lng;
        const popup = L.popup().setLatLng(e.latlng).setContent('<strong>Selected Location</strong><br>Latitude: ' + lat + '<br>Longitude: ' + lng).openOn(map);
        setTimeout(function () { map.closePopup(popup); }, 2500);
    });

    document.getElementById('searchLocation').addEventListener('click', function () {
        const query = document.getElementById('mapSearch').value;
        if (!query) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query + ' Bangladesh'))
            .then(function (response) { return response.json(); })
            .then(function (data) { if (data.length) { map.setView([data[0].lat, data[0].lon], 12); } });
    });

    document.getElementById('toggleSatellite').addEventListener('click', function () {
        const activeLayer = map.hasLayer(baseLayers.Satellite) ? baseLayers.Street : baseLayers.Satellite;
        map.removeLayer(baseLayers.Street);
        map.removeLayer(baseLayers.Satellite);
        activeLayer.addTo(map);
    });

    document.getElementById('locateMe').addEventListener('click', function () {
        map.locate({ setView: true, maxZoom: 12 });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const data = {
            latitude: latitudeInput.value,
            longitude: longitudeInput.value,
            incident_type: document.getElementById('incidentType').value,
            severity: document.getElementById('severity').value,
            description: document.getElementById('description').value,
            reporter: document.getElementById('reporter').value,
            address: 'Selected location',
            status: 'reported'
        };

        fetch('./api/map_incidents.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(function (response) { return response.json(); }).then(function (result) {
            feedback.classList.remove('d-none');
            feedback.textContent = result.success ? 'Report saved successfully.' : 'Unable to save report.';
            if (result.success) {
                form.reset();
                renderPins();
            }
        });
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('delete-pin')) {
            const id = event.target.getAttribute('data-id');
            fetch('./api/map_incidents.php?id=' + id, { method: 'DELETE' }).then(function () { window.location.reload(); });
        }
        if (event.target.classList.contains('resolve-pin')) {
            const id = event.target.getAttribute('data-id');
            fetch('./api/map_incidents.php', { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id, status: 'resolved' }) }).then(function () { window.location.reload(); });
        }
    });
});
