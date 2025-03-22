<!-- order_status.php -->
<?php
include 'inc/head.inc.php';
include 'inc/nav.inc.php';
session_start();
require 'db-connect.php';

// Check if order ID is provided
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($order_id <= 0) {
    die("Invalid order ID.");
}

// Fetch order status
$stmt = $conn->prepare("SELECT id, customer_name, total_price, status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Order statuses
$status_messages = [
    'Pending' => 'Your order has been placed and is waiting to be processed.',
    'Preparing' => 'Your order is being prepared by the restaurant.',
    'Out for Delivery' => 'A delivery driver has accepted your order and is on the way.',
    'Delivered' => 'Your order has been delivered successfully!'
];
?>

<div class="container mt-4">
    <h2>Order Status</h2>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
    <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
    <p><strong>Total Price:</strong> $<?= number_format($order['total_price'], 2) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
    <p><?= $status_messages[$order['status']] ?? 'Unknown status' ?></p>
    
    <a href="restaurants.php" class="btn btn-primary">Continue Browsing</a>
    <a href="chat_with_driver.php?order_id=<?= $order['id'] ?>" class="btn btn-info btn-sm">Chat</a>
</div>

<?php include 'inc/footer.inc.php'; ?>
