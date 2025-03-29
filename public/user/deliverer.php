<?php
require '../../db/db-connect.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /user/login.php?require_login=1");
    exit();
}

$NEW_ORDER_STATUS = 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>

    <?php include '../inc/nav.inc.php'; ?>

    <main class="container mt-4">
        <h2>Delivery Requests</h2>

        <div class="row">
            <?php
            $stmt = $conn->prepare("SELECT idOrders, customer_user_id, order_address, restaurant_id FROM Orders WHERE status=?");
            $stmt->bind_param("i", $NEW_ORDER_STATUS);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0):  // Check if there are orders
                while ($row = $result->fetch_assoc()):
                    $stmt = $conn->prepare("SELECT address FROM restaurant WHERE idrestaurant = ?");
                    $stmt->bind_param("i", $row['restaurant_id']);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $stmt->close();
                    $restaurant_address = $res->fetch_assoc()['address'] ?? 'Unknown Address';
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm" style="max-width: 350px;">
                            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                                <span>Upcoming</span>
                                <span>Nov 14 2:00 PM</span>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><i class="fa fa-user"></i> <strong>Scarlet Johansson</strong></p>
                                <p class="mb-1"><i class="bi-circle"></i>&nbsp;<?= $restaurant_address ?></p>
                                <p><i class="bi bi-geo-alt"></i>&nbsp;<?= $row['order_address'] ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center bg-light">
                                <span class="text-muted"><i class="bi bi-wallet"></i>&nbsp;SGD$ 1.99</span>
                                <button class="btn btn-success btn-sm"><a
                                        href="/user/deliverer_order_track.php?order_id=<?= $row['idOrders'] ?>">Accept
                                        Order</a></button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center text-muted">There are no orders currently available.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../inc/footer.inc.php'; ?>
    <script>
        getDeliveryRiderLocation();
        changeButtonText('Deliverer');
    </script>
</body>

</html>