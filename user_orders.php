<!-- user_orders.php -->
<?php
include 'inc/head.inc.php';
include 'inc/nav.inc.php';
session_start();
require 'db-connect.php';

// Assuming users are identified via session (Modify as per login system)
$customer_name = $_SESSION['user_name']; // Replace with session username if available

// Fetch current user orders
$stmt = $conn->prepare("SELECT id, total_price, status, created_at FROM orders WHERE customer_name = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $customer_name);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<div class="container mt-4">
    <h2>My Orders</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($order['id']) ?></td>
                        <td>$<?= number_format($order['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td>
                            <a href="order_status.php?order_id=<?= $order['id'] ?>" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no orders yet.</p>
    <?php endif; ?>
</div>

<?php include 'inc/footer.inc.php'; ?>
