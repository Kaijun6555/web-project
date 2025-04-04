<?php
require '../../db/db-connect.php';
session_start();

$google_api_key = "AIzaSyDBM2Uks3o02p1Vx9PAntKYvb-smBVzhCI";
$NEW_ORDER_STATUS = 1;
$restaurant_id = 0;
$total_price = 0;

// Retreieve Order Location
if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_long = $_SESSION['user_location']['long'];
    $user_address = $_SESSION['user_location']['address'];
} else {
    echo "No location saved yet.";
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Fetch whether order_ongoing
    $stmt = $conn->prepare("SELECT order_ongoing FROM Users WHERE idUsers = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: /");
    exit();
}

// If just checked out from cart, INSERT new Order items
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0 && $user['order_ongoing'] == 1) {

    if (isset($_SESSION['order'])) {

        unset($_SESSION['order']);
    }

    // Assign variables for order long and lat
    $order_long = $user_long;
    $order_lat = $user_lat;
    $order_address = $user_address;

    // Compute Total and retrieve restaurant ID
    foreach ($_SESSION['cart'] as $item) {
        $restaurant_id = $item['restaurant_id'];
        $total_price += $item['price'] * $item['quantity'];
    }

    // Insert order
    $stmt = $conn->prepare("INSERT INTO Orders (customer_user_id, total_price, restaurant_id, status, order_long, order_lat, order_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $customer_user_id = $user_id;
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

    // Store in session
    $_SESSION['order'][] = [
        "order_id" => $order_id,
        "restaurant_id" => $restaurant_id,
        'total_price' => $total_price,
    ];

    // Clear cart session
    unset($_SESSION['cart']);
}

if (isset($_SESSION['order'])) {
    $order_id = $_SESSION['order'][0]['order_id'];
    $restaurant_id = $_SESSION['order'][0]['restaurant_id'];
    $total_price = $_SESSION['order'][0]['total_price'];
}

// Fetch restaurant long and lat
$stmt = $conn->prepare("SELECT `long`, lat, name FROM restaurant WHERE idrestaurant = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();
$restaurant = $result->fetch_assoc();
$stmt->close();

// Array to store order details
$orderDetails = [];

// Retrieve Details of Order
$stmt = $conn->prepare("SELECT menu_item_id, quantity FROM Order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

// Retrieve Order Date from Mysql table
$stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d %r') AS formatted_date FROM Orders WHERE idOrders = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$order_date = $order['formatted_date'];
$stmt->close();

while ($item = $items->fetch_assoc()) {
    $menu_item_id = $item['menu_item_id'];
    $quantity = $item['quantity'];

    // Retrieve menu details
    $stmt = $conn->prepare("SELECT itemName, price FROM menu_item WHERE idmenu_item = ?");
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
    $menu_result = $stmt->get_result();

    if ($menu = $menu_result->fetch_assoc()) {
        $orderDetails[] = [
            'name' => $menu['itemName'],
            'quantity' => $quantity,
            'price' => $menu['price']
        ];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Track Order</title>
    <?php include '../inc/head.inc.php'; ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key ?>&libraries=places" async></script>

</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div id="map"></div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12 bg-light rounded p-4 orange-text">
                        <h3>Order Status <span id="order-status">Looking For Rider</span></h3>
                        <a href="/user/chat_with_driver.php?order_id=<?= $order_id ?>" class="btn btn-outline-primary">
                    Chat with Driver
                </a>
                <div class="text-center">
                            <div class="d-flex justify-content-between">
                                <div class="step">
                                    <span class="step-circle active" id="step1"></span>
                                    <div>Looking for Rider</div>
                                </div>
                                <div class="step">
                                    <span class="step-circle" id="step2"></span>
                                    <div>Rider found! Order is being prepared</div>
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
                <div class="row">
                    <div class="col-md-12 bg-light mt-4 p-4 rounded">
                        <div class="d-flex justify-content-between">
                            <h5>Order Summary</h5>
                        </div>
                        <p class="mb-1 text-muted"><?=$order_date?></p>
                        
                        <p class="mb-1"><strong>Restaurant</strong></p>
                        <p class="text-muted small"><?= $restaurant['name'] ?></p>

                        <div class="bg-white p-3 rounded">
                            <?php foreach ($orderDetails as $order_item): ?>
                                <div class="d-flex justify-content-between">
                                    <span><?= $order_item['quantity'] ?> X <?= $order_item['name'] ?></span>
                                    <strong>$<?= number_format($order_item['price'] * $order_item['quantity'], 2) ?></strong>
                                </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between text-success"><strong>Food Cost</strong>
                                <strong>$<?= number_format($total_price, 2) ?></strong>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between text-danger">
                                <span>Delivery Fee</span>
                                <strong>$1.99</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-success">
                                <strong>Total</strong>
                                <strong>$<?= number_format($total_price + 1.99, 2) ?></strong>
                            </div>
                        </div>
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
                        <button class="btn btn-success w-75 py-2"><a class="text-decoration-none text-white"
                                href="/">Back to Home</a></button>
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
                                    <strong>$<?= number_format($order_item['price'] * $order_item['quantity'], 2) ?></strong>
                                </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between text-success"><strong>Food Cost</strong>
                                <strong>$<?= number_format($total_price, 2) ?></strong>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between text-danger">
                                <span>Delivery Fee</span>
                                <strong>$1.99</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between text-success">
                                <strong>Total</strong>
                                <strong>$<?= number_format($total_price + 1.99, 2) ?></strong>
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
            if (step <= 5) {
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
                center: {
                    lat: <?= $user_lat ?>,
                    lng: <?= $user_long ?>
                },
                zoom: 17,
                disableDefaultUI: true,
                styles: [{
                        featureType: 'all',
                        elementType: 'labels',
                        stylers: [{
                            visibility: 'off'
                        }],
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry',
                        stylers: [{
                            color: '#ffffff'
                        }],
                    },
                ],
            });


            // Add the "Looking for a deliverer" marker
            marker = new google.maps.Marker({
                position: {
                    lat: <?= $user_lat ?>,
                    lng: <?= $user_long ?>
                }, // Starting point
                map: map,
                icon: '/static/searching-loading.gif',
                title: "Looking for a deliverer"
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: true
            });
        }


        // Request to constantly check order status.
        function checkOrderStatus(orderId) {
            fetch('/user/check_order_status.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        document.getElementById('order-status').innerText = data.status;
                        if (data.delivery_long && data.delivery_lat) {
                            updateMap(data.status, data.delivery_long, data.delivery_lat, data.step);
                        }
                    }
                    setTimeout(() => checkOrderStatus(orderId), 3000);
                })
                .catch(error => console.error('Error fetching order status:', error));
        }

        function updateMap(status, delivery_long, delivery_lat, stored_step) {

            // Handle Progress Bar
            if (step === 1) {
                step = stored_step;
                updateProgress();
            }

            if ((status === "Order is being Prepared" || status === "Rider Pickup")) {

                if (status === "Rider Pickup" && step === 2) {
                    step += 1;
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

                var end = {
                    lat: <?= $restaurant['lat'] ?>,
                    lng: <?= $restaurant['long'] ?>
                };

                directionsService.route({
                        origin: start,
                        destination: end,
                        travelMode: google.maps.TravelMode.WALKING
                    },
                    (response, status) => {
                        if (status === "OK") {
                            directionsRenderer.setDirections(response);
                            // Set marker at the delivery rider's location with start icon
                            var startMarker = new google.maps.Marker({
                                position: start,
                                map: map,
                                icon: {
                                    url: "https://foodfinder.shop/static/ridericon.png",
                                    scaledSize: new google.maps.Size(50, 50),
                                    anchor: new google.maps.Point(25, 50)
                                }
                            });

                            // Set marker at the restaurant's location with end icon
                            var endMarker = new google.maps.Marker({
                                position: end,
                                map: map,
                                icon: {
                                    url: "https://foodfinder.shop/static/foodplace.png",
                                    scaledSize: new google.maps.Size(50, 50),
                                    anchor: new google.maps.Point(25, 50)
                                }
                            });
                        } else {
                            alert("Directions request failed due to " + status);
                        }
                    }
                );
            } else if (status === "Rider is on the way") {

                if (step === 3) {
                    step += 1;
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
                var end = {
                    lat: <?= $user_lat ?>,
                    lng: <?= $user_long ?>
                };

                directionsService.route({
                        origin: start,
                        destination: end,
                        travelMode: google.maps.TravelMode.WALKING
                    },
                    (response, status) => {
                        if (status === "OK") {
                            directionsRenderer.setDirections(response);
                            // Set marker at the delivery rider's location with start icon
                            var startMarker = new google.maps.Marker({
                                position: start,
                                map: map,
                                icon: {
                                    url: "https://foodfinder.shop/static/ridericon.png",
                                    scaledSize: new google.maps.Size(50, 50),
                                    anchor: new google.maps.Point(25, 50)
                                }
                            });

                            // Set marker at the restaurant's location with end icon
                            var endMarker = new google.maps.Marker({
                                position: end,
                                map: map,
                                icon: {
                                    url: "https://foodfinder.shop/static/mylocation.png",
                                    scaledSize: new google.maps.Size(50, 50),
                                    anchor: new google.maps.Point(25, 50)
                                }
                            });
                        } else {
                            console.log("Directions request failed due to " + status);
                        }
                    }
                );
            } else if (status === "Order is delivered") {
                if (step === 4) {
                    step += 1;
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

        window.onload = initMap;
    </script>
</body>

</html>