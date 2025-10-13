<div>
    <style>
        #map {
            height: 530px;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
        }
        .gm-style-iw, .gm-style-iw-c {
            padding: 0 0 0 8px  !important;
            border: 2px solid transparent !important;
            border-image: linear-gradient(1deg, rgba(237, 25, 8, 1) 0%, rgba(32, 71, 244, 1) 100%) 1 !important;
        }
        .gm-style-iw-ch {
            display: none !important;
        }
        .gm-ui-hover-effect {
            margin-left: auto !important;
            font-weight: 500;
        }
        #sidebar {
            background: white;
            border-radius: 8px;
           border: 1px solid #ddd;
            padding: 4px;
            position: absolute;
            top: 10px;
            left: 10px;

            gap: 20px;
            z-index: 2;
        }
        .sidebar-list{
            overflow-y:auto ;
            max-height: 300px;
            scrollbar-width: thin;
            scrollbar-color: #15549e #f1f1f1;
        }
        .sidebar-list::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .sidebar-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .sidebar-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .sidebar-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .sidebar-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #sidebar li {
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            font-size: 14px;
        }
        #sidebar li:hover ,  #reset-button:hover{
            background: #f5f5f5;
        }
        #reset-button {
            /*background: #15549A;*/
            color: #778191;
            text-align: left;
            padding: 16px 0 10px 10px;
            cursor: pointer;
            font-weight: 400;
            border-bottom: 1px solid #ddd;
            display: flex ;
            gap: 6px;
            align-items: center;
        }

        .sidebar-list-div{
            height: 0;
            overflow: hidden;

        }
        .active-list{
            border: 1px solid #CECECE;
            border-radius: 8px;
            height: 300px;
            margin-top: 16px;
        }
        .filials{
            border: 1px solid #CECECE;
            border-radius: 8px;
            padding: 16px;
        }
    </style>

    <div class="custom-container section-space80">
        <h3 style="font-size: 40px; line-height: 56px; font-weight: 400; color: #0E1B2F; margin-bottom: 34px">{{ __('front.map.title') }}</h3>
        <div style="position: relative;">
            <div id="sidebar">
                    <div class="filials" onclick="setOpen()"> {{ __('front.map.filials') }}</div>
                <div class="sidebar-list-div">
                    <div id="reset-button" onclick="resetMap()">
                        <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                            <line x1="1" y1="1" x2="9" y2="9" stroke="#ff0000" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="9" y1="1" x2="1" y2="9" stroke="#ff0000" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        {{ __('front.map.reset') }}</div>
                    <div class="sidebar-list">
                        <ul id="locationList">
                        </ul>
                    </div>
                </div>
            </div>
            <div id="map"></div>
        </div>
    </div>

    <script>
        function  setOpen(){
            document.querySelector(".sidebar-list-div").classList.toggle('active-list')
        }
        const locations = [
                @foreach ($filials as $filial)
            {
                id: "{{ $filial->id }}",
                title: "{{ addslashes($filial->name) }}",
                address: "{{ addslashes($filial->address) }}",
                address2: "{{ addslashes($filial->description) }}",
                contact_phone: "{{ addslashes($filial->contact_phone) }}",
                coords: {
                    lat: {{ $filial->latitude }},
                    lng: {{ $filial->longitude }}
                }
            }@if (!$loop->last),@endif
            @endforeach
        ];

        const locationList = document.getElementById('locationList');
        if (locations.length > 0) {
            locationList.innerHTML = locations.map((location, index) => `<li id="location-${index}" onclick="setLocation('${location.id}')">${location.title}</li>`).join('');
        } else {
            locationList.innerHTML = `<li>No location</li>`;
        }
    </script>

    <script>
        let markers = [];
        let map;
        let infoWindow;
        let mapLoaded = false;
        const initialCenter = { lat: 40.143105, lng: 47.576927 };
        const initialZoom = 7.2;

        function esi() {
            const silverStyle = [
                { elementType: 'geometry', stylers: [{ color: '#f5f5f5' }] },
                { elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
                { elementType: 'labels.text.fill', stylers: [{ color: '#616161' }] },
                { elementType: 'labels.text.stroke', stylers: [{ color: '#f5f5f5' }] },
                {
                    featureType: 'administrative.land_parcel',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#2a2a2a' }]
                },
                {
                    featureType: 'administrative',
                    elementType: 'geometry',
                    stylers: [{ color: '#2a2a2a' }]
                },
                {
                    featureType: 'poi',
                    elementType: 'geometry',
                    stylers: [{ color: '#eeeeee' }]
                },
                {
                    featureType: 'poi',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#757575' }]
                },
                {
                    featureType: 'poi.park',
                    elementType: 'geometry',
                    stylers: [{ color: '#e5e5e5' }]
                },
                {
                    featureType: 'poi.park',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#9e9e9e' }]
                },
                {
                    featureType: 'road',
                    elementType: 'geometry',
                    stylers: [{ color: '#ffffff' }]
                },
                {
                    featureType: 'road.arterial',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#757575' }]
                },
                {
                    featureType: 'road.highway',
                    elementType: 'geometry',
                    stylers: [{ color: '#dadada' }]
                },
                {
                    featureType: 'road.highway',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#616161' }]
                },
                {
                    featureType: 'road.local',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#9e9e9e' }]
                },
                {
                    featureType: 'transit.line',
                    elementType: 'geometry',
                    stylers: [{ color: '#333' }]
                },
                {
                    featureType: 'transit.station',
                    elementType: 'geometry',
                    stylers: [{ color: '#eeeeee' }]
                },
                {
                    featureType: 'water',
                    elementType: 'geometry',
                    stylers: [{ color: '#c9c9c9' }]
                },
                {
                    featureType: 'water',
                    elementType: 'labels.text.fill',
                    stylers: [{ color: '#9e9e9e' }]
                }
            ];

            map = new google.maps.Map(document.getElementById("map"), {
                center: initialCenter,
                zoom: initialZoom,
                styles: silverStyle,
                mapTypeControl: false,
                keyboardShortcuts: false,
                fullscreenControl: false,
                streetViewControl: false,
            });

            infoWindow = new google.maps.InfoWindow();
            mapLoaded = true;

            locations.forEach(loc => {
                const marker = new google.maps.Marker({
                    position: loc.coords,
                    map: map,
                    title: loc.title
                });

                marker.addListener("click", () => {
                    map.panTo(marker.getPosition());
                    map.setZoom(16);
                    infoWindow.setContent(`
                    <div>
                        <strong style="font-size: 14px; color: #000; font-weight: 500; margin-bottom: 14px">${loc.title}</strong>
                        <p style="margin: 14px 0 0 0; font-size: 12px">${loc.address}<br>
                        ${loc.address2 || ''}</p>
                        <p style="margin: 14px 0 0 0; font-size: 12px">Tel: <b style="font-weight: 500">${loc?.contact_phone ?? "Mövcud deyil"}</b></p>
                        <a style="color: #15549A; font-weight: 500; font-size: 16px" href="https://www.google.com/maps/search/?api=1&query=${loc.coords.lat},${loc.coords.lng}" target="_blank" rel="noopener">
                            Google Xəritələrdə gör
                        </a>
                    </div>
                `);
                    infoWindow.open(map, marker);
                });
                markers.push({ marker, loc });
            });
        }

        function setLocation(id) {
            if (!mapLoaded) {
                console.warn('Map is not loaded yet. Please wait.');
                return;
            }

            const selected = markers.find(m => m.loc.id === id);
            if (selected) {
                map.panTo(selected.marker.getPosition());
                map.setZoom(15);
                const loc = selected.loc;
                infoWindow.setContent(`
                <div>
                    <strong style="font-size: 14px; color: #000; font-weight: 500; margin-bottom: 14px">${loc.title}</strong>
                    <p style="margin: 14px 0 0 0; font-size: 12px">${loc.address}<br>
                    ${loc.address2 || ''}</p>
                    <p style="margin: 14px 0 0 0; font-size: 12px">Tel: <b style="font-weight: 500">${loc?.contact_phone ?? "Mövcud deyil"}</b></p>
                    <a style="color: #15549A; font-weight: 500; font-size: 16px" href="https://www.google.com/maps/search/?api=1&query=${loc.coords.lat},${loc.coords.lng}" target="_blank" rel="noopener">
                        Google Xəritələrdə gör
                    </a>
                </div>
            `);
                infoWindow.open(map, selected.marker);
            } else {
                console.error('Location not found:', id);
            }
        }

        function resetMap() {
            if (!mapLoaded) {
                console.warn('Map is not loaded yet. Please wait.');
                return;
            }
            map.setCenter(initialCenter);
            map.setZoom(initialZoom);
            infoWindow.close();
        }
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCZT0suJ0bGTswU9W-7PcEMU_6P870BveM&callback=esi"></script>
</div>