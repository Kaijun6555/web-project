<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Location & Directions</title>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBM2Uks3o02p1Vx9PAntKYvb-smBVzhCI&libraries=places"></script>
    <style>
        #map {
            height: 500px;
            width: 100%;
        }

        #controls {
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <h2>Live Location & Directions</h2>
    <div id="map"></div>

    <div id="controls">
        <input type="text" id="destination" placeholder="Enter Destination (e.g., New York)">
        <button onclick="navigateToDestination()">Get Directions</button>
    </div>

    <script>
        let map, userMarker, destinationMarker, directionsService, directionsRenderer;

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: 0, lng: 0 },
                zoom: 15,
            });

            userMarker = new google.maps.Marker({
                map,
                title: "Delivery Ride Location",
                icon: {
                    url: "https://foodfinder.shop/static/mylocation.png", // Custom user icon
                    scaledSize: new google.maps.Size(50, 50),
                },
            });

            destinationMarker = new google.maps.Marker({
                map,
                title: "Food Place",
                icon: {
                    url: "https://img.icons8.com/color/48/marker.png", // Custom destination icon
                    scaledSize: new google.maps.Size(50, 50),
                },
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: true, // 👈 Prevents default A & B markers
            });
            directionsRenderer.setMap(map);

            trackLocation();
        }

        function trackLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (position) => {
                        const userPos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };

                        userMarker.setPosition(userPos);
                        map.setCenter(userPos);
                    },
                    (error) => {
                        console.error("Error getting location:", error);
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showDirections(destination) {
            if (!userMarker.getPosition()) {
                alert("Waiting for location...");
                return;
            }

            destinationMarker.setPosition(destination); // Place destination marker

            const request = {
                origin: userMarker.getPosition(),
                destination: destination,
                travelMode: google.maps.TravelMode.WALKING,
            };

            directionsService.route(request, (result, status) => {
                if (status === google.maps.DirectionsStatus.OK) {
                    directionsRenderer.setDirections(result);
                } else {
                    alert("Could not get directions: " + status);
                }
            });
        }

        function navigateToDestination() {
            const destinationInput = document.getElementById("destination").value;
            if (!destinationInput) {
                alert("Please enter a destination.");
                return;
            }

            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: destinationInput }, (results, status) => {
                if (status === google.maps.GeocoderStatus.OK) {
                    const destination = results[0].geometry.location;
                    showDirections(destination);
                } else {
                    alert("Destination not found: " + status);
                }
            });
        }

        window.onload = initMap;
    </script>

</body>

</html>