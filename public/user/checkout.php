<!-- checkout.php -->
<?php
require '../../db/db-connect.php';

session_start();

$restaurant_id = 0;

if (empty($_SESSION['user_id'])) {
    header("Location: /user/login.php?require_login=1&checkout=1");
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: /");
    exit();
}

// Check for location
if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_lon = $_SESSION['user_location']['long'];
    $user_address = $_SESSION['user_location']['address'];
}

// Calculate total price and retrieve restaurant_id
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $restaurant_id = $item['restaurant_id'];
    $total_price += $item['price'] * $item['quantity'];
}

// Fetch restaurant address and details
$stmt = $conn->prepare("SELECT `long`, lat, address, name FROM restaurant WHERE idrestaurant = ?");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();
$restaurant = $result->fetch_assoc();
$stmt->close();

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
        <!-- Header -->
        <div class="text-center mb-4">
            <h2 class="fw-bold">Place Order and Checkout</h2>
            <p class="fs-5"><?= $restaurant['name'] ?></p>
        </div>

        <!-- Delivery Section -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 p-4 border rounded shadow-sm">
                <h4 class="mb-3">Deliver to</h4>
                <p class="text-muted">Delivery arrival time <strong>45 min (2 km away)</strong></p>

                <!-- Address and Map -->
                <div class="row">
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" value="<?= $user_address?>" readonly>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label">Address details (Floor, Unit, Building)</label>
                        <input type="text" class="form-control" value="">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label">Note to delivery rider</label>
                        <input type="text" class="form-control" placeholder="e.g. Meet me at the lobby">
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-6 p-4 border rounded shadow-sm">
                <h4 class="mb-3">Order Summary</h4>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-secondary btn-sm me-2">–</button>
                                <span>1</span>
                                <button class="btn btn-outline-secondary btn-sm ms-2">+</button>
                                <img src="<?= htmlspecialchars($item['image']) ?>" class="rounded me-3 custom-size"
                                    alt="Item Image">
                                <div>
                                    <p class="mb-0 fw-bold"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="text-muted mb-0">Smoky BBQ McShaker™ Fries</p>
                                </div>
                                <span class="ms-auto fw-bold">$<?= number_format($item['price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>

                <!-- Subtotal and Service Fees -->
                <div class="row">
                    <div class="col-12 d-flex justify-content-between">
                        <p class="mb-0">Subtotal</p>
                        <p class="fw-bold">$<?= number_format($total_price, 2) ?></p>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <p class="mb-0">Delivery fees <i class="bi bi-info-circle"></i></p>
                        <p class="fw-bold">$<?= number_format(1.99, 2) ?></p>
                    </div>
                    <hr>
                    <div class="col-12 d-flex justify-content-between">
                        <p class="mb-0">Total</p>
                        <p class="fw-bold">$<?= number_format($total_price + 1.99, 2) ?></p>
                    </div>
                </div>
                <form action="<?= $paypal_url ?>" method="post">
                    <!-- PayPal Business Email -->
                    <input type="hidden" name="business" value="<?= $business_email ?>">
                    <!-- PayPal Settings -->
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="item_name" value="Food Order">
                    <input type="hidden" name="amount" value="<?= $total_price ?>">
                    <input type="hidden" name="currency_code" value="SGD">
                    <input type="hidden" name="return" value="<?= $return_url ?>">
                    <input type="hidden" name="cancel_return" value="<?= $cancel_url ?>">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success w-100">Pay with PayPal</button>
                </form>
            </div>
        </div>
    </div>


</body>

</html>