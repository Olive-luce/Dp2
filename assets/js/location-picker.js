document.addEventListener('DOMContentLoaded', function () {
    const pickers = document.querySelectorAll('[data-location-picker]');
    if (!pickers.length || typeof L === 'undefined') return;

    const BANGLADESH_CENTER = [23.6850, 90.3563];

    const reverseGeocode = function (lat, lng) {
        const url = 'https://nominatim.openstreetmap.org/reverse?format=json&zoom=16&lat=' + lat + '&lon=' + lng;
        return fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(function (data) { return data && data.display_name ? data.display_name : ''; })
            .catch(function () { return ''; });
    };

    pickers.forEach(function (picker) {
        const canvas = picker.querySelector('[data-picker-map]');
        const latInput = picker.querySelector('[data-picker-lat]');
        const lngInput = picker.querySelector('[data-picker-lng]');
        const addressInput = picker.querySelector('[data-picker-address]');
        const status = picker.querySelector('[data-picker-status]');
        const searchInput = picker.querySelector('[data-picker-search]');
        const searchButton = picker.querySelector('[data-picker-search-button]');
        const locateButton = picker.querySelector('[data-picker-locate]');

        const map = L.map(canvas, { zoomControl: true }).setView(BANGLADESH_CENTER, 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = null;

        const setStatus = function (text) {
            if (status) status.textContent = text;
        };

        const selectLocation = function (lat, lng, lookupAddress) {
            const latitude = Number(lat).toFixed(6);
            const longitude = Number(lng).toFixed(6);

            latInput.value = latitude;
            lngInput.value = longitude;

            if (marker) {
                marker.setLatLng([latitude, longitude]);
            } else {
                marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    const position = marker.getLatLng();
                    selectLocation(position.lat, position.lng, true);
                });
            }

            if (lookupAddress === false) {
                setStatus('Location selected.');
                return;
            }

            setStatus('Looking up the address…');
            reverseGeocode(latitude, longitude).then(function (address) {
                if (address) {
                    addressInput.value = address;
                    setStatus('Address found. Edit it if a landmark is clearer.');
                } else {
                    setStatus('No address found for this point — describe it yourself.');
                }
            });
        };

        map.on('click', function (event) {
            selectLocation(event.latlng.lat, event.latlng.lng, true);
        });

        if (searchButton && searchInput) {
            const runSearch = function () {
                const query = searchInput.value.trim();
                if (!query) return;
                setStatus('Searching…');
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query + ' Bangladesh'))
                    .then(function (response) { return response.json(); })
                    .then(function (results) {
                        if (!results.length) {
                            setStatus('No place matched that search.');
                            return;
                        }
                        map.setView([results[0].lat, results[0].lon], 15);
                        addressInput.value = results[0].display_name;
                        selectLocation(results[0].lat, results[0].lon, false);
                        setStatus('Found it. Click the map to fine-tune the exact spot.');
                    })
                    .catch(function () { setStatus('Search is unavailable right now.'); });
            };

            searchButton.addEventListener('click', runSearch);
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    runSearch();
                }
            });
        }

        if (locateButton) {
            locateButton.addEventListener('click', function () {
                setStatus('Finding your location…');
                map.locate({ setView: true, maxZoom: 16 });
            });

            map.on('locationfound', function (event) {
                selectLocation(event.latlng.lat, event.latlng.lng, true);
            });

            map.on('locationerror', function () {
                setStatus('Could not get your location — pick the spot on the map instead.');
            });
        }

        if (latInput.value && lngInput.value) {
            selectLocation(latInput.value, lngInput.value, false);
            map.setView([latInput.value, lngInput.value], 14);
        }

        // Leaflet mis-measures a map rendered inside a card that was still laying out.
        setTimeout(function () { map.invalidateSize(); }, 200);
    });
});
