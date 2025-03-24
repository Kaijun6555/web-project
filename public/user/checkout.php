<!-- checkout.php -->
<?php
session_start();
require '../../db/db-connect.php';

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
$paypal_url = "https://sandbox.paypal.com";
$business_email = "sb-vs71r4881472@business.example.com";
$return_url = "/user/payment_success.php";
$cancel_url = "/user/payment_cancel.php";
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
        <a href="/" class="btn btn-secondary mt-3">Back to Cart</a>
    </div>
    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>