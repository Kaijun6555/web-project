<!-- restaurant.php -->
<?php
include 'inc/head.inc.php';
include 'inc/nav.inc.php';
require 'db-connect.php'; 
session_start(); // Start session for cart functionality

// Get restaurant ID from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid restaurant ID.");
}

// Fetch restaurant details
$stmt = $conn->prepare("SELECT name, description, address FROM restaurants WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$restaurant = $result->fetch_assoc();
$stmt->close();

if (!$restaurant) {
    die("Restaurant not found.");
}

// Fetch menu items for the restaurant
$stmt = $conn->prepare("SELECT id, name, description, price FROM menu_items WHERE restaurant_id = ? ORDER BY name");
$stmt->bind_param("i", $id);
$stmt->execute();
$menu_result = $stmt->get_result();
$stmt->close();

// Handle add to cart action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["menu_id"])) {
    $menu_id = (int) $_POST["menu_id"];
    $menu_name = $_POST["menu_name"];
    $menu_price = (float) $_POST["menu_price"];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add item to cart
    $_SESSION['cart'][] = [
        'id' => $menu_id,
        'name' => $menu_name,
        'price' => $menu_price,
        'quantity' => 1
    ];
    
    header("Location: restaurant.php?id=$id&cart_success=1");
    exit();
}
?>

<div class="container mt-4">
    <h2><?= htmlspecialchars($restaurant['name']) ?></h2>
    <p><strong>Address:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>
    <p><?= nl2br(htmlspecialchars($restaurant['description'])) ?></p>
    <a href="restaurants.php" class="btn btn-primary">Back to Restaurants</a>
    
    <h3 class="mt-4">Menu</h3>
    <?php if ($menu_result->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($menu_item = $menu_result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($menu_item['name']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($menu_item['description'])) ?><br>
                    <span class="text-success">$<?= number_format($menu_item['price'], 2) ?></span>
                    
                    <!-- Add to Cart Form -->
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="menu_id" value="<?= $menu_item['id'] ?>">
                        <input type="hidden" name="menu_name" value="<?= htmlspecialchars($menu_item['name']) ?>">
                        <input type="hidden" name="menu_price" value="<?= $menu_item['price'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Add to Cart</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No menu items available.</p>
    <?php endif; ?>
    
    <!-- Show Cart Success Message -->
    <?php if (isset($_GET['cart_success'])): ?>
        <div class="alert alert-success mt-3">Item added to cart!</div>
    <?php endif; ?>
</div>

<?php include 'inc/footer.inc.php'; ?>
