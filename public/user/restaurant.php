<!-- restaurant.php -->
<?php
require '../../db/db-connect.php';
session_start();

// Get restaurant ID from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid restaurant ID.");
}

// Fetch restaurant details
$stmt = $conn->prepare("SELECT name, address FROM restaurant WHERE idrestaurant = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$restaurant = $result->fetch_assoc();
$stmt->close();

if (!$restaurant) {
    die("Restaurant not found.");
}

// Fetch menu items for the restaurant
$stmt = $conn->prepare("SELECT idmenu_item, itemName, description, price, availability, image FROM menu_item WHERE restaurant_id = ? ORDER BY itemName");
$stmt->bind_param("i", $id);
$stmt->execute();
$menu_result = $stmt->get_result();
$stmt->close();

// Handle add to cart action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["menu_id"])) {
    $menu_id = (int) $_POST["menu_id"];
    $menu_name = $_POST["menu_name"];
    $menu_price = (float) $_POST["menu_price"];
    $restaurant_id = (int) $_POST["restaurant_id"];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item to cart
    $_SESSION['cart'][] = [
        'id' => $menu_id,
        'restaurant_id' => $restaurant_id,
        'name' => $menu_name,
        'price' => $menu_price,
        'quantity' => 1
    ];

    header("Location: /user/restaurant.php?id=$id&cart_success=1");
    exit();
}
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

        <p>
            <a href="/">Home</a>
            <i class="bi bi-arrow-right-short"></i>
            <a href="restaurants.php">Restaurants</a>
            <i class="bi bi-arrow-right-short"></i>
            <?= htmlspecialchars($restaurant['name']) ?>
        </p>

        <h2><?= htmlspecialchars($restaurant['name']) ?></h2>
        <p><strong>Address:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>
        <h3 class="mt-4">Menu</h3>
        <!-- Show Cart Success Message -->
        <?php if (isset($_GET['cart_success'])): ?>
            <div class="alert alert-success mt-3">Item added to cart!</div>
        <?php endif; ?>
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
                        </div>
                        <div class="offcanvas-body">
                            <p>Customize your meal by selecting options below:</p>
                            <h6>Choice of Sauce</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sauce" id="sauce1">
                                <label class="form-check-label" for="sauce1">Curry Sauce</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sauce" id="sauce2">
                                <label class="form-check-label" for="sauce2">BBQ Sauce</label>
                            </div>

                            <h6 class="mt-3">Sides</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fries">
                                <label class="form-check-label" for="fries">French Fries (L)</label>
                            </div>

                            <!-- Quantity and Add to Basket Button -->
                            <div class="d-flex mt-4">
                                <button class="btn btn-outline-secondary">-</button>
                                <input type="text" class="form-control text-center mx-2" value="1">
                                <button class="btn btn-outline-secondary">+</button>
                            </div>

                            <!-- Add to Cart Form -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="menu_id" value="<?= $menu_item['idmenu_item'] ?>">
                                <input type="hidden" name="menu_name" value="<?= htmlspecialchars($menu_item['itemName']) ?>">
                                <input type="hidden" name="menu_price" value="<?= $menu_item['price'] ?>">
                                <input type="hidden" name="restaurant_id" value="<?= $menu_item['restaurant_id'] ?>">
                                <button class="btn btn-success w-100 mt-3" type="submit">
                                    Add to Basket - <?= number_format($menu_item['price'], 2) ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No menu items available.</p>
        <?php endif; ?>
    </div>

    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>