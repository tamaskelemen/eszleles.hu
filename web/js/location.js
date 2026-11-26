(function locationSettings($, L) {
    let mapElement = document.getElementById('location-map');

    if (!mapElement || !L) {
        return;
    }

    // the usage policy of Nominatim: at most one request per second, searching on submit only
    const NOMINATIM = 'https://nominatim.openstreetmap.org/';

    let latitudeInput = $('#location-latitude');
    let longitudeInput = $('#location-longitude');
    let nameInput = $('#location-name');
    let deviceMessage = $('#location-device-message');
    let searchMessage = $('#location-search-message');
    let results = $('#location-results');
    let reverseRequest = null;

    let map = L.map(mapElement).setView([Number(mapElement.dataset.latitude), Number(mapElement.dataset.longitude)], Number(mapElement.dataset.zoom));
    let marker = null;

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>-közreműködők',
    }).addTo(map);

    function formatCoordinates(latitude, longitude) {
        let format = function (value) {
            return Math.abs(value).toFixed(2).replace('.', ',') + '°';
        };

        return format(latitude) + ' ' + (latitude >= 0 ? 'É' : 'D') + ', ' + format(longitude) + ' ' + (longitude >= 0 ? 'K' : 'Ny');
    }

    function placeMarker(latitude, longitude) {
        if (marker === null) {
            marker = L.marker([latitude, longitude], { draggable: true, keyboard: true, title: 'A kiválasztott pont' }).addTo(map);
            marker.on('dragend', function () {
                let position = marker.getLatLng();
                choose(position.lat, position.lng);
            });
        } else {
            marker.setLatLng([latitude, longitude]);
        }
    }

    /**
     * @param {number} latitude
     * @param {number} longitude
     * @param {string|null} name null: it is looked up
     * @param {number|null} zoom null: the map stays
     */
    function choose(latitude, longitude, name, zoom) {
        // longitudes of a map dragged around the world
        longitude = ((longitude + 540) % 360) - 180;

        latitudeInput.val(latitude.toFixed(5));
        longitudeInput.val(longitude.toFixed(5));
        $('#location-coordinates').text(formatCoordinates(latitude, longitude));
        $('#location-chosen').prop('hidden', false);
        placeMarker(latitude, longitude);

        if (zoom) {
            map.setView([latitude, longitude], zoom);
        }

        if (name) {
            nameInput.val(name);
        } else {
            lookUpName(latitude, longitude);
        }
    }

    function lookUpName(latitude, longitude) {
        if (reverseRequest) {
            reverseRequest.abort();
        }

        reverseRequest = $.getJSON(NOMINATIM + 'reverse', {
            format: 'jsonv2',
            lat: latitude,
            lon: longitude,
            zoom: 10,
            'accept-language': 'hu',
        }).done(function (place) {
            let address = place.address || {};
            nameInput.val(address.city || address.town || address.village || address.municipality || place.name || 'Saját helyszín');
        }).fail(function (xhr, status) {
            if (status !== 'abort' && !nameInput.val()) {
                nameInput.val('Saját helyszín');
            }
        });
    }

    map.on('click', function (e) {
        choose(e.latlng.lat, e.latlng.lng, null, null);
    });

    if (mapElement.dataset.hasPoint === '1') {
        placeMarker(Number(mapElement.dataset.latitude), Number(mapElement.dataset.longitude));
    }

    $('#location-device').on('click', function () {
        if (!window.isSecureContext || !navigator.geolocation) {
            deviceMessage.text('Ez a böngésző (vagy a nem titkosított kapcsolat) nem engedi a helymeghatározást. Keress rá a településre, vagy jelöld ki a térképen!');
            return;
        }

        let button = $(this).prop('disabled', true);
        deviceMessage.text('Helyzet lekérdezése…');

        navigator.geolocation.getCurrentPosition(function (position) {
            button.prop('disabled', false);
            deviceMessage.text('Megvan! Ellenőrizd a térképen, majd mentsd el.');
            choose(position.coords.latitude, position.coords.longitude, null, 11);
        }, function (error) {
            button.prop('disabled', false);
            deviceMessage.text(error.code === error.PERMISSION_DENIED
                ? 'Nem engedélyezted a helymeghatározást. Keress rá a településre, vagy jelöld ki a térképen!'
                : 'Nem sikerült meghatározni a helyzetedet. Keress rá a településre, vagy jelöld ki a térképen!');
        }, { enableHighAccuracy: false, timeout: 15000, maximumAge: 600000 });
    });

    $('#location-search').on('submit', function (e) {
        e.preventDefault();

        let query = $('#location-query').val().trim();

        if (!query) {
            return;
        }

        results.empty();
        searchMessage.text('Keresés…');

        $.getJSON(NOMINATIM + 'search', {
            format: 'jsonv2',
            q: query,
            limit: 5,
            'accept-language': 'hu',
        }).done(function (places) {
            searchMessage.text(places.length ? 'Válaszd ki a megfelelőt:' : 'Nincs ilyen nevű hely. Próbáld másképp, vagy jelöld ki a térképen!');

            places.forEach(function (place) {
                let button = $('<button type="button" class="location-result">').text(place.display_name);

                button.on('click', function () {
                    results.empty();
                    searchMessage.text('');
                    choose(Number(place.lat), Number(place.lon), place.name || place.display_name.split(',')[0], 11);
                });

                results.append($('<li>').append(button));
            });
        }).fail(function () {
            searchMessage.text('A keresés most nem elérhető. Jelöld ki a helyet a térképen!');
        });
    });
})(jQuery, window.L);
