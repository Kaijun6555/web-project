<?php
require '../../db/db-connect.php';
session_start();

if (!$_SESSION['restaurant_id'] == null) {
    $restaurant_id = $_SESSION['restaurant_id'];
} else {
    die("Not Logged in");
}

$stmt = $conn->prepare("SELECT idmenu_item, itemName, price, availability, description, image FROM menu_item WHERE restaurant_id = ? ORDER BY itemName");
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$menu_result = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <?php include '../inc/nav_restaurant.inc.php'; ?>
    <div class="container-fluid">
        <div class="button ml-3">
            <button type="button" class="btn btn-success" onclick="printProducts()">Print</button>
            <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#addProductModal">Add Product</button>
        </div>
        <div class="row">
            <div class="col min-vh-100 py-3">
                <!-- toggler -->
                <button class="btn float-end" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" role="button">
                    <i class="bi bi-arrow-right-square-fill fs-3" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvas"></i>
                </button>
                Your Products:
                <?php if ($menu_result->num_rows > 0): ?>
                    <div class="row card-deck">
                        <?php while ($menu_item = $menu_result->fetch_assoc()): ?>
                            <div class="card col-md-4">
                                <img class="card-img-top" src="<?= htmlspecialchars($menu_item['image']) ?>" alt="Card image cap">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($menu_item['itemName']) ?></h5>
                                    <p class="card-text"><?= nl2br(htmlspecialchars($menu_item['description'])) ?></p>
                                    <span class="text-success">$<?= number_format($menu_item['price'], 2) ?></span>
                                    <button class="btn btn-success rounded-circle" data-bs-toggle="offcanvas"
                                        data-bs-target="#foodDetail">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="foodDetail">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title"><?= htmlspecialchars($menu_item['itemName']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                    <!-- Quantity and Add to Basket Button -->
                                    <div class="d-flex mt-4">
                                        <button class="btn btn-outline-secondary">-</button>
                                        <input type="text" class="form-control text-center mx-2" value="1">
                                        <button class="btn btn-outline-secondary">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No menu items available.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>