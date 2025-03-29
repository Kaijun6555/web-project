<?php
$google_api_key = "AIzaSyDBM2Uks3o02p1Vx9PAntKYvb-smBVzhCI";
$PREPARING_ORDER_STATUS = 2;
?>

<!-- Send out deliver requests by storing order -->
<?php
require '../../db/db-connect.php';
session_start();

// Check if Deliverer's Location is set
if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_long = $_SESSION['user_location']['long'];
}

// Check if User is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

if (!isset($_GET['order_id'])) {
    header("Location: /user/deliverer.php");
    exit();
}

$order_id = $_GET['order_id'];

// Prepare the statement to check the current status
$stmt = $conn->prepare("SELECT status FROM Orders WHERE idOrders = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($current_status);
$stmt->fetch();
$stmt->close();

// Check if the status is 1 before proceeding with the update
if ($current_status == 1) {
    $stmt = $conn->prepare("UPDATE Orders SET delivery_user_id = ?, status = ? WHERE idOrders = ?");
    $stmt->bind_param("iii", $user_id, $PREPARING_ORDER_STATUS, $order_id);
    $stmt->execute();
    $stmt->close();
}

// Retrieve information about the order
$stmt = $conn->prepare("SELECT idOrders, customer_user_id, order_address, restaurant_id, order_long, order_lat FROM Orders WHERE idOrders = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result();
$stmt->close();

if ($row = $order->fetch_assoc()) {  // Fetch order details
    $restaurant_id = $row['restaurant_id'];  // Get restaurant_id
    $order_long = $row['order_long'];
    $order_lat = $row['order_lat'];

    // Now fetch the longitude and latitude of the restaurant
    $stmt = $conn->prepare("SELECT `long`, lat, name FROM restaurant WHERE idrestaurant = ?");
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($restaurant = $res->fetch_assoc()) {  // Fetch restaurant details
        $restaurant_long = $restaurant['long'];
        $restaurant_lat = $restaurant['lat'];
    } else {
        echo "No restaurant found!";
    }
    $stmt->close();
} else {
    echo "No order found!";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Deliverer Track Order</title>
    <?php include '../inc/head.inc.php'; ?>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key ?>&callback=initMap"></script>

    <script
        src="https://www.paypal.com/sdk/js?client-id=AYX3VoAfHt6l59ysb2FMJejhy4yFe670slGGzQw9H7R5ezdH8yfGzAhdeX2rn9mrWER6YxQi9eKXi-3E&components=buttons"></script>

</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div id="map" style="height:500px;"></div>
            </div>
            <div class="col-md-6">
                <h3>Order Status: <strong><span id="order-status">Order is being Prepared</span></strong></h3>
                <button class="btn btn-success" onclick="orderCollected(this)">Order Collected from Restaurant</button>
                <button class="btn btn-success" onclick="orderCompleted(this)">Order Completed</button>
            </div>
            <!-- Order Completed Modal -->
            <div class="modal fade" id="orderCompleted" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content p-4">
                        <div class="modal-body row">
                            <div class="col-lg-4 text-center p-4">
                                <div class="rounded-circle d-inline-block">
                                    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" width="50">
                                </div>
                                <h4 class="mt-3">Hooray! You have completed an order.</h4>
                                <p class="text-muted">Please enter your paypal details to receive your payment</p>
                                <!-- Input form for PayPal email -->
                                <form id="paypalForm" method="post">
                                    <label for="paypalEmail">Enter PayPal Email:</label>
                                    <input type="email" id="paypalEmail" name="paypalEmail" required />
                                    <br><br>
                                    <button type="submit" class="btn btn-success">Send Payout</button>
                                </form>


                            </div>

                            <div class="col-lg-8 bg-light p-4 rounded">

                                <div class="d-flex justify-content-between">
                                    <h5>Delivery Summary</h5>
                                </div>

                                <p class="mb-1"><strong>Date</strong></p>

                                <p class="mb-1"><strong>Restaurant</strong></p>

                                <p class="text-muted small"><?= $restaurant['name'] ?></p>

                                <div class="mt-3">
                                    <div class="d-flex justify-content-between text-danger">
                                        <span>Delivery Fee</span>
                                        <strong>1.99</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between text-success">
                                        <strong>Total amount receivable</strong>
                                        <strong>$1.99</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script defer>


        let map;
        let directionsService;
        let directionsRenderer;
        let userMarker;
        let watchId;
        let currentDestination;

        function initMap() {
            // Initialize map with error handling and improved configuration
            try {
                // Restaurant's initial location (from PHP or dynamic source)
                currentDestination = {
                    lat: <?= $restaurant_lat ?>,
                    lng: <?= $restaurant_long ?>
                };

                // Create map with enhanced options
                map = new google.maps.Map(document.getElementById("map"), {
                    center: currentDestination,
                    zoom: 14,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    styles: [
                        // Optional: Add custom map styling for better readability
                        {
                            featureType: "poi",
                            elementType: "labels",
                            stylers: [{ visibility: "off" }]
                        }
                    ]
                });

                // Initialize directions service and renderer
                directionsService = new google.maps.DirectionsService();
                directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: true, // Hide default markers
                    polylineOptions: {
                        strokeColor: '#0000FF',
                        strokeOpacity: 0.8,
                        strokeWeight: 5
                    }
                });

                // Add destination marker
                new google.maps.Marker({
                    position: currentDestination,
                    map: map,
                    title: "Restaurant Location",
                    icon: {
                        url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                        scaledSize: new google.maps.Size(40, 40)
                    }
                });

                // Check geolocation support with comprehensive error handling
                if ("geolocation" in navigator) {
                    initializeUserTracking();
                } else {
                    handleGeolocationError("Geolocation not supported");
                }
            } catch (error) {
                console.error("Map initialization error:", error);
                alert("Unable to load map. Please refresh or check your connection.");
            }
        }

        function initializeUserTracking() {
            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Create or update user marker
                    if (!userMarker) {
                        userMarker = new google.maps.Marker({
                            position: userLocation,
                            map: map,
                            title: "Your Location",
                            icon: {
                                url: "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 24 24' fill='%23007bff'><path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'/></svg>",
                                scaledSize: new google.maps.Size(30, 30)
                            }
                        });
                    } else {
                        userMarker.setPosition(userLocation);
                    }

                    // Calculate and display route
                    calculateAndDisplayRoute(userLocation, currentDestination);
                },
                handleGeolocationError,
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }

        function calculateAndDisplayRoute(origin, destination) {
            directionsService.route(
                {
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.TravelMode.WALKING
                },
                (response, status) => {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionsRenderer.setDirections(response);

                        // Optional: Display route distance and duration
                        const route = response.routes[0];
                        const leg = route.legs[0];
                        displayRouteInfo(leg.distance.text, leg.duration.text);
                    } else {
                        console.error("Directions request failed:", status);
                    }
                }
            );
        }

        function displayRouteInfo(distance, duration) {
            const infoElement = document.getElementById('route-info');
            if (infoElement) {
                infoElement.innerHTML = `Distance: ${distance} | Estimated Time: ${duration}`;
            }
        }

        function changeDestination(lat, lng, name = "New Destination") {
            try {
                currentDestination = { lat, lng };

                // Recenter map
                map.setCenter(currentDestination);

                // Update destination marker
                new google.maps.Marker({
                    position: currentDestination,
                    map: map,
                    title: name,
                    icon: {
                        url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                        scaledSize: new google.maps.Size(40, 40)
                    }
                });

                // Trigger route recalculation
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const userLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };
                            calculateAndDisplayRoute(userLocation, currentDestination);
                        },
                        handleGeolocationError
                    );
                }
            } catch (error) {
                console.error("Destination change error:", error);
            }
        }

        function handleGeolocationError(error) {
            let errorMessage = "Unknown error";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "Location access denied. Please enable location permissions.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Location information unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "Location request timed out.";
                    break;
            }
            console.error(errorMessage);

            // Optional: Display error to user
            const errorElement = document.getElementById('location-error');
            if (errorElement) {
                errorElement.textContent = errorMessage;
            }
        }

        function stopTracking() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
        }
        // Cleanup function for page unload or component destruction
        window.addEventListener('unload', stopTracking);
        // Handle the form submission
        document.getElementById('paypalForm').addEventListener('submit', function (event) {
            event.preventDefault();

            var paypalEmail = document.getElementById('paypalEmail').value;

            // Send the PayPal email and amount to the backend
            fetch('/requests/process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    paypalEmail: paypalEmail,
                    amount: 1.99
                })
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    alert("Payment has been made to your email! Thank you for delivering with us.");
                    window.location.href = "/";
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error occurred while sending payout');
                });
        });

        // Request to constantly check order status. So if Restaurant has prepared order, user is notified
        function checkOrderStatus(orderId) {
            SaveDeliveryRiderLocation(orderId);
            fetch('/user/check_order_status.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        document.getElementById('order-status').innerText = data.status;
                        updateMap(data.status)
                    }
                    setTimeout(() => checkOrderStatus(orderId), 3000); // Check every 3 seconds
                })
                .catch(error => console.error('Error fetching order status:', error));
        }

        function updateMap(status) {
            if (status === "Deliverer is on the way") {
                changeDestination(<?= $order_lat ?>, <?= $order_long ?>);
            }
        }

        function orderCollected(button) {
            fetch('/requests/process_order_pickup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: "order_id=" + <?= $order_id ?>
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                })
                .catch(error => console.error("Error:", error));

        }

        function orderCompleted(button) {
            fetch('/requests/process_order_delivered.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: "order_id=" + <?= $order_id ?>
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.success) {
                        alert("delivery completed");
                        // Display Completion Modal
                        deliveryComplete();
                    }

                })
                .catch(error => console.error("Error:", error));
        }

        function deliveryComplete() {
            setTimeout(() => {
                // Display pop up after 3 seconds.
                let myModal = new bootstrap.Modal(document.getElementById('orderCompleted'));
                myModal.show();
            }, 3000);
        }

        // Call the check order status function
        checkOrderStatus(<?= $order_id ?>);

    </script>
</body>

</html>