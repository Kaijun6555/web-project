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

// Prepare the SELECT statement to check the current status
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
    $stmt = $conn->prepare("SELECT `long`, lat FROM restaurant WHERE idrestaurant = ?");
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
                <button class="btn btn-success" onclick="orderDelivered(this)">Order Delivered to Customer</button>
            </div>

        </div>
    </div>

    <script>

        // Request to constantly check order status. So if Restaurant has prepared order, user is notified
        function checkOrderStatus(orderId) {
            getLocation();
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

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(sendPosition, showError);
            } else {
                document.getElementById("output").innerText = "Geolocation is not supported by this browser.";
            }
        }

        function sendPosition(position) {
            let latitude = position.coords.latitude;
            let longitude = position.coords.longitude;

            // Store Delivery Rider's Current Location
            fetch('/requests/process_deliverer_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `latitude=${latitude}&longitude=${longitude}&order_id=${<?= $order_id ?>}`
            })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error => alert(error));
        }

        function showError(error) {
            console.log("Can't get Location");
        }

        let map, directionsService, directionsRenderer, userMarker, watchId;

        function initMap() {

            var destination = { lat: <?= $restaurant_lat ?>, lng: <?= $restaurant_long ?> }; // Restaurant's Location long and lat

            map = new google.maps.Map(document.getElementById("map"), {
                center: destination,
                zoom: 14,
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer();

            directionsRenderer.setMap(map);

            if (navigator.geolocation) {
                watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        const userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };

                        if (!userMarker) {
                            userMarker = new google.maps.Marker({
                                position: userLocation,
                                map: map,
                                title: "You are here",
                            });
                        } else {
                            userMarker.setPosition(userLocation);
                        }

                        calculateRoute(userLocation, destination);
                    },
                    (error) => console.error("Error getting location:", error),
                    { enableHighAccuracy: true }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function calculateRoute(origin, destination) {
            directionsService.route(
                {
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.TravelMode.WALKING,
                },
                (response, status) => {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionsRenderer.setDirections(response);
                    } else {
                        console.error("Directions request failed:", status);
                    }
                }
            );
        }

        function changeDestination(lat, lng) {
            destination = { lat: lat, lng: lng };
            console.log("Destination changed to:", destination);

            // Recalculate the route with the new destination
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    calculateRoute(userLocation, destination);
                });
            }
        }

        function orderCollected(button) {
            console.log("Order Collected");
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
                    button.disable = true;
                })
                .catch(error => console.error("Error:", error));

        }

        function orderDelivered(button) {
            console.log("Delivery Complete");
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
                })
                .catch(error => console.error("Error:", error));

        }

        // Call the check order status function
        checkOrderStatus(<?= $order_id ?>);

    </script>
</body>

</html>