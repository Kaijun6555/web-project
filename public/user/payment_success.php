<!-- payment_success.php -->
<?php
include '../inc/head.inc.php';
include '../inc/nav.inc.php';
session_start();
require '../../db/db-connect.php';

// Check if payment was successful
// if (!isset($_GET['tx'])) {
//     header("Location: /");
//     exit();
// }
// $transaction_id = $_GET['tx'];

$total_price = 0;

// Save order to database
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {

    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    $restaurant_id = $_SESSION['cart'][0]['restaurant_id'];
    
    echo $restaurant_id;
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO Orders (customer_user_id, total_price, restaurant_id) VALUES (?, ?, ?)");
    $customer_user_id = $_SESSION['user_id'];
    $stmt->bind_param("idi", $customer_user_id, $total_price, $restaurant_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO Order_items (order_id, menu_item_id, price) VALUES (?, ?, ?)");
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
    <!-- <p><strong>Transaction ID:</p> -->
    <p><strong>Total Paid:</strong> $<?= number_format($total_price, 2) ?></p>
    <a href="restaurants.php" class="btn btn-primary">Continue Browsing</a>
</div>

<?php include '../inc/footer.inc.php'; ?>