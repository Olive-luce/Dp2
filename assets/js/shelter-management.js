document.addEventListener('DOMContentLoaded', function () {
    const shelterForm = document.getElementById('shelterForm');
    const shelterList = document.getElementById('shelterList');
    const shelterMap = document.getElementById('shelterMap');

    const loadShelters = function () {
        fetch('./../api/shelters.php')
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!Array.isArray(data)) return;
                if (shelterList) {
                    shelterList.innerHTML = '';
                    data.forEach(function (shelter) {
                        const row = document.createElement('tr');
                        row.innerHTML = '<td>' + shelter.name + '</td><td>' + shelter.address + '</td><td>' + shelter.capacity + '</td><td>' + shelter.current_occupancy + '</td><td>' + shelter.status + '</td>';
                        shelterList.appendChild(row);
                    });
                }
                if (shelterMap && window.L) {
                    const map = window.shelterMapInstance || L.map('shelterMap').setView([23.8103, 90.4125], 7);
                    window.shelterMapInstance = map;
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);
                    map.eachLayer(function (layer) {
                        if (layer instanceof L.Marker) {
                            map.removeLayer(layer);
                        }
                    });
                    data.forEach(function (shelter) {
                        const marker = L.marker([23.8 + Math.random() * 0.05, 90.3 + Math.random() * 0.1]).addTo(map);
                        marker.bindPopup('<strong>' + shelter.name + '</strong><br>' + shelter.address + '<br>Status: ' + shelter.status);
                    });
                }
            });
    };

    if (shelterForm) {
        shelterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(shelterForm);
            const payload = {
                name: formData.get('name'),
                address: formData.get('address'),
                capacity: formData.get('capacity'),
                current_occupancy: formData.get('current_occupancy'),
                contact_person: formData.get('contact_person'),
                status: formData.get('status')
            };
            fetch('./../api/shelters.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function () { loadShelters(); shelterForm.reset(); });
        });
    }

    loadShelters();
});
