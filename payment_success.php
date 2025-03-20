<!-- payment_success.php -->
<?php
include 'inc/head.inc.php';
include 'inc/nav.inc.php';
session_start();
require 'db-connect.php';

// Check if payment was successful
if (!isset($_GET['tx'])) {
    header("Location: cart.php");
    exit();
}

$transaction_id = $_GET['tx']; // PayPal transaction ID
$total_price = 0;

// Save order to database
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, total_price) VALUES (?, ?)");
    $customer_name = $_SESSION['user_name']; // Can be modified to use session user data
    $stmt->bind_param("sd", $customer_name, $total_price);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, price) VALUES (?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) {
        $stmt->bind_param("iid", $order_id, $item['id'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();
    
    // Clear cart session
    unset($_SESSION['cart']);
}
?>

<div class="container mt-4">
    <h2>Payment Successful!</h2>
    <p>Your order has been placed successfully.</p>
    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction_id) ?></p>
    <p><strong>Total Paid:</strong> $<?= number_format($total_price, 2) ?></p>
    <a href="restaurants.php" class="btn btn-primary">Continue Browsing</a>
</div>

<?php include 'inc/footer.inc.php'; ?>
