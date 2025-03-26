<!-- checkout.php -->
<?php
require '../../db/db-connect.php';

session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /user/login.php?require_login=1&checkout=1");
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: /");
    exit();
}

// Calculate total price
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// PayPal Sandbox Settings
$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
$business_email = "sb-vcr9d39023297@business.example.com";
$return_url = "https://www.foodfinder.shop/user/track_order.php";
$cancel_url = "https://www.foodfinder.shop/";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card mt-5">
                    <div class="card-body">
                        <h4 class="card-title">Greetings Foodie!</h4>
                        <h2 class="card-subtitle mb-2 text-muted">Where are you looking to deliver your food to?</h2>
                        <br>
                        <div class="form-group">
                            <!-- <label for="location">Enter Location</label> -->
                            <input type="text" class="form-control" id="location" name="location"
                                placeholder="Type an Address" onfocus="getLocation()">
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="row">
            <h2>Checkout</h2>
            <p><strong>Total Price:</strong> $<?= number_format($total_price, 2) ?></p>

            <form action="<?= $paypal_url ?>" method="post">
                <!-- PayPal Business Email -->
                <input type="hidden" name="business" value="<?= $business_email ?>">

                <!-- PayPal Settings -->
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="item_name" value="Food Order">
                <input type="hidden" name="amount" value="<?= $total_price ?>">
                <input type="hidden" name="currency_code" value="USD">
                <input type="hidden" name="return" value="<?= $return_url ?>">
                <input type="hidden" name="cancel_return" value="<?= $cancel_url ?>">

                <!-- Submit Button -->
                <button type="submit" class="btn btn-success">Pay with PayPal</button>
            </form>
        </div>
    </div>
    <?php include '../inc/footer.inc.php'; ?>
    <script>
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
            document.getElementById("location").value = latitude + ", " + longitude;
            fetch('/requests/process_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `latitude=${latitude}&longitude=${longitude}`
            })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error => console.log(error));
        }

        function showError(error) {
            document.getElementById("location").value = "Can't Get Location";
        }
    </script>
</body>

</html>