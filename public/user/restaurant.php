<!-- restaurant.php -->
<?php
require '../../db/db-connect.php';
session_start();

// Get restaurant ID from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid restaurant ID.");
}

// Check for location
if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_lon = $_SESSION['user_location']['long'];
    $user_address = $_SESSION['user_location']['address'];
} else {
    header("Location: /?require_location=1");
    exit();
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
$stmt = $conn->prepare("SELECT idmenu_item, itemName, description, price, restaurant_id, availability, image FROM menu_item WHERE restaurant_id = ? ORDER BY itemName");
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
    $menu_image = $_POST["menu_image"];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;


    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item to cart
    $_SESSION['cart'][] = [
        'id' => $menu_id,
        'restaurant_id' => $id,
        'name' => $menu_name,
        'price' => $menu_price,
        'quantity' => $quantity,
        'image' => $menu_image
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
    <main>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="location" name="location" placeholder="Type an Address"
                        onfocus="getUserLocation()" oninput="toggleSubmitButton()" value="<?= $user_address ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn text-muted background-orange" id="location-button" onclick="saveLocation()" disabled>
                        <strong>Confirm Address</strong>
                    </button>
                </div>
            </div>

            <br>

            <!-- Navigation -->
            <p>
                <a href="/">Home</a>
                <i class="bi bi-arrow-right-short"></i>
                <a href="restaurants.php">Restaurants</a>
                <i class="bi bi-arrow-right-short"></i>
                <?= htmlspecialchars($restaurant['name']) ?>
            </p>

            <h1><?= htmlspecialchars($restaurant['name']) ?></h1>
            <p><strong>Address:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>
            <h2 class="mt-4">Menu</h2>
            <!-- Show Cart Success Message -->
            <?php if (isset($_GET['cart_success'])): ?>
                <div class="alert alert-success mt-3">Item added to cart!</div>
            <?php endif; ?>
            <?php if ($menu_result->num_rows > 0): ?>
                <div class="row card-deck">
                    <?php while ($menu_item = $menu_result->fetch_assoc()): ?>
                        <div class="card col-md-4">
                            <img class="card-img-top" src="<?= htmlspecialchars($menu_item['image']) ?>" alt="Image of menu item">
                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($menu_item['itemName']) ?></h3>
                                <p class="card-text"><?= nl2br(htmlspecialchars($menu_item['description'])) ?></p>
                                <span class="text-success">$<?= number_format($menu_item['price'], 2) ?></span>
                                <button class="btn btn-success rounded-circle" data-bs-toggle="offcanvas" aria-label="Add item"
                                    data-bs-target="#foodDetail<?= $menu_item['idmenu_item'] ?>">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="foodDetail<?= $menu_item['idmenu_item'] ?>" aria-label="offcanvasLabel">
                            <div class="offcanvas-header">
                                <h3 class="offcanvas-title"><?= htmlspecialchars($menu_item['itemName']) ?></h3>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <p>Customize your meal by selecting options below:</p>
                                <h4>Choice of Sauce</h4>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sauce" id="sauce1">
                                    <label class="form-check-label" for="sauce1">Curry Sauce</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sauce" id="sauce2">
                                    <label class="form-check-label" for="sauce2">BBQ Sauce</label>
                                </div>

                                <h4 class="mt-3">Sides</h4>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="fries">
                                    <label class="form-check-label" for="fries">French Fries (L)</label>
                                </div>

                                <!-- Quantity and Add to Basket Button -->
                                <div class="d-flex align-items-center mt-4">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity('qty<?= $menu_item['idmenu_item'] ?>', -1)">-</button>
                                    <input type="number" class="form-control text-center mx-2" name="quantity" id="qty<?= $menu_item['idmenu_item'] ?>"
                                        value="1" min="1" style="max-width: 80px;"
                                        oninput="updateButtonPrice(<?= $menu_item['idmenu_item'] ?>, <?= $menu_item['price'] ?>)" aria-label="QTY">
                                    <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity('qty<?= $menu_item['idmenu_item'] ?>', 1)">+</button>
                                </div>

                                <!-- Add to Cart Form -->
                                <form method="POST" class="d-inline mt-3">
                                    <input type="hidden" name="menu_id" value="<?= $menu_item['idmenu_item'] ?>">
                                    <input type="hidden" name="menu_image" value="<?= $menu_item['image'] ?>">
                                    <input type="hidden" name="menu_name" value="<?= htmlspecialchars($menu_item['itemName']) ?>">
                                    <input type="hidden" name="menu_price" value="<?= $menu_item['price'] ?>">
                                    <input type="hidden" name="restaurant_id" value="<?= $menu_item['restaurant_id'] ?>">
                                    <input type="hidden" name="quantity" id="formQty<?= $menu_item['idmenu_item'] ?>" value="1">
                                    <button
                                        class="btn btn-success w-100 mt-3"
                                        type="submit"
                                        id="addBtn<?= $menu_item['idmenu_item'] ?>"
                                        data-base-price="<?= $menu_item['price'] ?>"
                                        onclick="syncQuantityBeforeSubmit('qty<?= $menu_item['idmenu_item'] ?>', 'formQty<?= $menu_item['idmenu_item'] ?>')">
                                        Add to Basket - $<?= number_format($menu_item['price'], 2) ?>
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
    </main>
    <?php include '../inc/footer.inc.php'; ?>
    <script>
        function changeQuantity(inputId, delta) {
            const input = document.getElementById(inputId);
            let current = parseInt(input.value);
            if (isNaN(current)) current = 1;
            current += delta;
            if (current < 1) current = 1;
            input.value = current;
            const id = inputId.replace('qty', '');
            const price = parseFloat(document.getElementById('addBtn' + id).dataset.basePrice);
            updateButtonPrice(id, price);
        }

        function syncQuantityBeforeSubmit(sourceId, targetId) {
            const source = document.getElementById(sourceId);
            const target = document.getElementById(targetId);
            target.value = source.value;
        }

        function updateButtonPrice(id, unitPrice) {
            const qty = parseInt(document.getElementById('qty' + id).value) || 1;
            const total = (qty * unitPrice).toFixed(2);
            const button = document.getElementById('addBtn' + id);
            button.innerText = `Add to Basket - $${total}`;
        }
    </script>

</body>

</html>