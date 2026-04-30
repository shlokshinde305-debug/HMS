<!DOCTYPE html>
<html>
<head>
    <title>Live Student Tracking</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        body { font-family: Arial; margin: 0; padding: 10px; background:#f5f5f5; }
        #map { height: 600px; margin-top:10px; }

        .panel {
            background:#fff;
            padding:10px;
            border-radius:10px;
            box-shadow:0 2px 6px rgba(0,0,0,0.2);
            margin-bottom:10px;
        }

        .alert-red { color:red; margin-bottom:5px; }
        .alert-green { color:green; margin-bottom:5px; }
    </style>
</head>
<body>

<h2>📍 Live Student Tracking</h2>

<!-- 🔔 Notification Panel -->
<div class="panel">
    <h3>🔔 Live Alerts</h3>
    <div id="alerts" style="height:150px; overflow-y:auto;"></div>
</div>

<!-- 🗺️ Map -->
<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
// 📍 Hostel Location
const hostelLat = 16.72946;
const hostelLng = 74.24185;
const radius = 200; // meters

// 🗺️ Map Init
var map = L.map('map').setView([hostelLat, hostelLng], 16);

// 🌍 Tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

// 🟢 Boundary Circle
L.circle([hostelLat, hostelLng], {
    color: 'green',
    fillColor: '#0f0',
    fillOpacity: 0.2,
    radius: radius
}).addTo(map).bindPopup("Hostel Boundary");

// 📍 Markers store
let markers = {};

// 🚨 Notification tracker
let notified = {};

// 🔄 Load Locations
function loadLocations(){
    fetch('http://localhost/HMS/modules/get_locations.php')
    .then(res => res.json())
    .then(data => {

        let alertsBox = document.getElementById("alerts");

        data.forEach(student => {

            let id = student.student_id;
            let lat = parseFloat(student.latitude);
            let lng = parseFloat(student.longitude);

            // 📏 Distance
            let distance = map.distance([hostelLat, hostelLng], [lat, lng]);
            let isInside = distance <= radius;

            // 🎨 Marker icon
            let icon = L.icon({
                iconUrl: isInside
                    ? 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                iconSize: [32, 32]
            });

            // 📍 Marker update/create
            if(markers[id]){
                markers[id].setLatLng([lat, lng]);
                markers[id].setIcon(icon);
            } else {
                markers[id] = L.marker([lat, lng], {icon: icon})
                    .addTo(map)
                    .bindPopup(`Student ID: ${id}`);
            }

            let time = new Date().toLocaleTimeString();

            // 🚨 OUTSIDE alert
            if(!isInside && !notified[id]){
                notified[id] = true;

                alertsBox.innerHTML =
                    `<div class="alert-red">
                        ⚠️ Student ${id} left hostel at ${time}
                    </div>` + alertsBox.innerHTML;
            }

            // ✅ RETURN alert
            if(isInside && notified[id]){
                notified[id] = false;

                alertsBox.innerHTML =
                    `<div class="alert-green">
                        ✅ Student ${id} returned inside at ${time}
                    </div>` + alertsBox.innerHTML;
            }

        });

    });
}

// ▶️ Run once
loadLocations();

// 🔁 Auto refresh
setInterval(loadLocations, 5000);

</script>

</body>
</html>