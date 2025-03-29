<?php

$google_api_key = "AIzaSyDBM2Uks3o02p1Vx9PAntKYvb-smBVzhCI";
$NEW_ORDER_STATUS = 1;
$restaurant_id = 0;

// find API to Change value according to long lat
$order_address = "SIT Ho Bee Auditorium, 1 Punggol Coast Road";

?>

<!-- Send out deliver requests by storing order -->
<?php
require '../../db/db-connect.php';
session_start();

// Retreieve Order Location
if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_long = $_SESSION['user_location']['long'];
} else {
    echo "No location saved yet.";
}

// Total price of order
$total_price = 0;

// Order long and lat
$order_long = $user_long;
$order_lat = $user_lat;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {

    // Compute total
    foreach ($_SESSION['cart'] as $item) {
        $restaurant_id = $item['restaurant_id'];
        $total_price += $item['price'] * $item['quantity'];
    }

    // Fetch restaurant long and lat
    $stmt = $conn->prepare("SELECT `long`, lat, name FROM restaurant WHERE idrestaurant = ?");
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $restaurant = $result->fetch_assoc();
    $stmt->close();

    // Insert order
    $stmt = $conn->prepare("INSERT INTO Orders (customer_user_id, total_price, restaurant_id, status, order_long, order_lat, order_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $customer_user_id = $_SESSION['user_id'];
    $stmt->bind_param(
        "idiidds",
        $customer_user_id,
        $total_price,
        $restaurant_id,
        $NEW_ORDER_STATUS,
        $order_long,
        $order_lat,
        $order_address
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO Order_items (order_id, menu_item_id, price, quantity) VALUES (?, ?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) {
        $stmt->bind_param("iidi", $order_id, $item['id'], $item['price'], $item['quantity']);
        $stmt->execute();
    }
    $stmt->close();

    // Clear cart session
    unset($_SESSION['cart']);

    $stmt = $conn->prepare("SELECT menu_item_id, quantity FROM Order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result();
    $stmt->close();

    // Array to store order details
    $orderDetails = [];

    while ($item = $items->fetch_assoc()) {
        $menu_item_id = $item['menu_item_id'];
        $quantity = $item['quantity'];

        // Retrieve menu details using menu_item_id
        $stmt = $conn->prepare("SELECT itemName FROM menu_item WHERE idmenu_item = ?");
        $stmt->bind_param("i", $menu_item_id);
        $stmt->execute();
        $menu_result = $stmt->get_result();

        if ($menu = $menu_result->fetch_assoc()) {
            $orderDetails[] = [
                'name' => $menu['itemName'],
                'quantity' => $quantity
            ];
        }
        $stmt->close();
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Track Order</title>
    <?php include '../inc/head.inc.php'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key ?>&callback=initMap" async
        defer></script>

</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div id="map" style="height: 500px;"></div>
            </div>
            <div class="col-md-6">
                <h3>Order Status <span id="order-status">Looking For Rider</span></h3>
                <div class="text-center">
                    <div class="d-flex justify-content-between">
                        <div class="step">
                            <span class="step-circle active" id="step1"></span>
                            <div>Looking for Rider</div>
                        </div>
                        <div class="step">
                            <span class="step-circle" id="step2"></span>
                            <div>Order being prepared</div>
                        </div>
                        <div class="step">
                            <span class="step-circle" id="step3"></span>
                            <div>Order ready to be Picked Up</div>
                        </div>
                        <div class="step">
                            <span class="step-circle" id="step4"></span>
                            <div>Order is On the way!</div>
                        </div>
                        <div class="step">
                            <span class="step-circle" id="step5"></span>
                            <div>Delivered</div>
                        </div>
                    </div>

                    <div class="progress mt-2">
                        <div id="progress-bar" class="progress-bar bg-success" style="width: 20%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Order Delivered Modal -->
    <div class="modal fade" id="orderDelivered" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content p-4">
                <div class="modal-body row">
                    <div class="col-lg-4 text-center p-4">
                        <div class="rounded-circle d-inline-block">
                            <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" width="50">
                        </div>
                        <h4 class="mt-3">Your Order has been Delivered!</h4>
                        <p class="text-muted">Hope you enjoyed your delivery experience</p>
                        <button class="btn btn-success w-75 py-2"><a class="text-decoration-none text-white" href="/">Back to Home</a></button>
                    </div>

                    <div class="col-lg-8 bg-light p-4 rounded">
                        <div class="d-flex justify-content-between">
                            <h5>Order Summary</h5>
                        </div>
                        <p class="mb-1"><strong>Date</strong></p>
                        <p class="text-muted small">User name</p>

                        <p class="mb-1"><strong>Restaurant</strong></p>
                        <p class="text-muted small"><?= $restaurant['name'] ?></p>

                        <div class="bg-white p-3 rounded">
                            <?php foreach ($orderDetails as $order_item): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= $order_item['quantity'] ?> X <?= $order_item['name'] ?></span>
                                    <strong>9.9</strong>
                                </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between text-success"><strong>Food Cost</strong>
                                <strong><?= $total_price ?></strong>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between text-danger">
                                <span>Delivery Fee</span>
                                <strong>1.99</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-success">
                                <strong>Total</strong>
                                <strong>$<?= $total_price + 1.99 ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script defer>

        // Update the Progress Bar
        let step = 1;
        function updateProgress() {
            if (step < 5) {
                step++;
                document.getElementById("progress-bar").style.width = (step * 20) + "%";

                for (let i = 1; i <= step; i++) {
                    document.getElementById("step" + i).classList.add("active");
                }
            }
        }


        // Map Variables
        let map, marker, directionsService, directionsRenderer;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: <?= $user_lat ?>, lng: <?= $user_long ?> },
                zoom: 17,
                disableDefaultUI: true,
                styles: [
                    {
                        featureType: 'all',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }],
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry',
                        stylers: [{ color: '#ffffff' }],
                    },
                ],
            });


            // Add the "Looking for a deliverer" marker
            marker = new google.maps.Marker({
                position: { lat: <?= $user_lat ?>, lng: <?= $user_long ?> }, // Starting point
                map: map,
                icon: '/static/searching-loading.gif', // You can use an animated icon for this state
                title: "Looking for a deliverer"
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({ suppressMarkers: false });
        }


        // Request to constantly check order status.
        function checkOrderStatus(orderId) {
            fetch('/user/check_order_status.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        document.getElementById('order-status').innerText = data.status;
                        updateMap(data.status, data.delivery_long, data.delivery_lat);
                    }
                    setTimeout(() => checkOrderStatus(orderId), 3000);
                })
                .catch(error => console.error('Error fetching order status:', error));
        }

        function updateMap(status, delivery_long, delivery_lat) {
            if (status === "Order is being Prepared" || status === "Rider Pickup") {

                // Handle Progress Bar
                if (step === 1) {
                    updateProgress();
                }
                if (status === "Rider Pickup" && step === 2) {
                    updateProgress();
                }

                // Resetting the map.
                marker.setMap(null);
                directionsRenderer.setMap(map);

                // Delivery Rider Live Location
                var start = {
                    lat: parseFloat(delivery_lat),
                    lng: parseFloat(delivery_long)
                };
                
                var end = { lat: <?= $restaurant['lat'] ?>, lng: <?= $restaurant['long'] ?> };

                directionsService.route(
                    {
                        origin: start,
                        destination: end,
                        travelMode: google.maps.TravelMode.WALKING
                    },
                    (response, status) => {
                        if (status === "OK") {
                            directionsRenderer.setDirections(response);
                        } else {
                            alert("Directions request failed due to " + status);
                        }
                    }
                );
            }

            else if (status === "Rider is on the way") {

                if (step === 3) {
                    updateProgress();
                }

                // Order on the way
                marker.setMap(null);
                directionsRenderer.setMap(map);

                // Live location of delivery rider
                var start = {
                    lat: parseFloat(delivery_lat),
                    lng: parseFloat(delivery_long)
                };

                <?php
                $stmt = $conn->prepare("SELECT order_long, order_lat FROM Orders WHERE idOrders = ?");
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $order = $stmt->get_result();
                $stmt->close();
                ?>

                // Location of customer
                var end = { lat: <?= $order_lat ?>, lng: <?= $order_long ?> };
                directionsService.route(
                    {
                        origin: start,
                        destination: end,
                        travelMode: google.maps.TravelMode.WALKING
                    },
                    (response, status) => {
                        if (status === "OK") {
                            directionsRenderer.setDirections(response);
                        } else {
                            console.log("Directions request failed due to " + status);
                        }
                    }
                );
            }
            else if (status === "Order is delivered") {
                if (step === 4) {
                    updateProgress();
                    deliveryComplete();
                    return
                }
            }
        }

        function deliveryComplete() {
            setTimeout(() => {
                // Display pop up after 3 seconds.
                let myModal = new bootstrap.Modal(document.getElementById('orderDelivered'));
                myModal.show();
            }, 3000);
        }

        checkOrderStatus(<?= $order_id ?>);

    </script>
</body>

</html>