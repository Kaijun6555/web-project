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
        src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key ?>&libraries=places"></script>

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
                <button class="btn btn-success" id="order_collected_button" onclick="orderCollected()" disabled>Order Collected from Restaurant</button>
                <button class="btn btn-success" id="order_completed_button" onclick="orderCompleted()" disabled>Order Completed</button>
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
                                    <button type="submit" class="btn btn-success">Confirm Email</button>
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
                                        <strong>$1.99</strong>
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

        // Map Variables
        let map, marker, directionsService, directionsRenderer, startMarker, endMarker;

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
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({ suppressMarkers: true });
        }

        function updateMap(status, delivery_long, delivery_lat) {
            if ((status === "Order is being Prepared" || status === "Rider Pickup")) {

                if(status === "Rider Pickup"){
                    document.getElementById('order_collected_button').disabled = false;
                }

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
                            // Set marker at the delivery rider's location with start icon
                            startMarker = new google.maps.Marker({
                                position: start,
                                map: map,
                                icon: {
                                    url: "https://foodfinder.shop/static/ridericon.png",
                                    scaledSize: new google.maps.Size(50, 50),
                                    anchor: new google.maps.Point(25, 50)
                                }
                            });

                            // Set marker at the restaurant's location with end icon
                            endMarker = new google.maps.Marker({
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
            }


            else if (status === "Rider is on the way") {

                document.getElementById('order_collected_button').disabled = true;
                document.getElementById('order_completed_button').disabled = false

                // Order on the way
                marker.setMap(null);
                directionsRenderer.setMap(map);

                // Live location of delivery rider
                var start = {
                    lat: parseFloat(delivery_lat),
                    lng: parseFloat(delivery_long)
                };

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
            }

        }


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
                    alert("Payment has been made to your email! Thank you for delivering with us.");
                    setTimeout(() => {
                        window.location.href = "/";
                    }, 500); 
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
                    if (data.status != "Order is delivered") {
                        document.getElementById('order-status').innerText = data.status;
                        if (data.delivery_long && data.delivery_lat) {
                            updateMap(data.status, data.delivery_long, data.delivery_lat);
                        }
                    }
                    setTimeout(() => checkOrderStatus(orderId), 3000); // Check every 3 seconds
                })
                .catch(error => console.error('Error fetching order status:', error));
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

        window.onload = initMap;
    </script>
</body>

</html>