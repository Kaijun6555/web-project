<?php
$PREPARING_ORDER_STATUS = 2;

require '../../db/db-connect.php';
session_start();

if (empty($_SESSION['restaurant_id'])) {
    die("Not Logged In");
}

$id = $_SESSION['restaurant_id'];

// Fetch all orders for restaurant
$stmt = $conn->prepare("SELECT idOrders, total_price, created_at FROM Orders WHERE restaurant_id = ? AND status = ? ORDER BY created_at");
$stmt->bind_param("ii", $id, $PREPARING_ORDER_STATUS);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <?php include '../inc/nav_restaurant.inc.php'; ?>
            <div class="col py-3">
                <?php if ($orders->num_rows > 0): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">Pending Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="pendingOrders">
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#<?= $order['idOrders'] ?>">
                                                <?= $order['idOrders'] ?>
                                                <span class="badge bg-warning text-dark ms-2">Pending</span>
                                                <button class="btn btn-success"
                                                    onclick="completeOrder(<?= $order['idOrders'] ?>, this)">Order
                                                    Completed</button>
                                            </button>
                                        </h2>
                                        <div id="<?= $order['idOrders'] ?>" class="accordion-collapse collapse"
                                            data-bs-parent="#pendingOrders">
                                            <div class="accordion-body">
                                                <p>
                                                    <strong>Order Time</strong>
                                                    <?= $order['created_at'] ?>
                                                </p>
                                                <div>
                                                    <strong>Items:</strong>
                                                    <?php
                                                    $stmt = $conn->prepare("SELECT menu_item_id, quantity FROM Order_items WHERE order_id = ?");
                                                    $stmt->bind_param("i", $order['idOrders']);
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
                                                    ?>
                                                    <?php foreach ($orderDetails as $order_item): ?>
                                                        <p><?= $order_item['quantity'] ?> X <?= $order_item['name'] ?></p>
                                                    <?php endforeach; ?>
                                                </div>
                                                <p><strong>Total: <?= $order['total_price'] ?></strong></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <!-- <div class="card-footer text-end">
                            <button class="btn btn-primary btn-sm">View All</button>
                        </div> -->
                    </div>
                <?php else: ?>
                    <p>No Orders Available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function completeOrder(orderId, button) {
            fetch('/requests/process_complete_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: "order_id=" + orderId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let accordionItem = button.closest(".accordion-item");
                        if (accordionItem) {
                            accordionItem.remove();
                        }
                    } else {
                        alert("Failed to complete the order. Please try again.");
                    }
                })
                .catch(error => console.error("Error:", error));
        }
    </script>
</body>

</html>