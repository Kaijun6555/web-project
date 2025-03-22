<!-- cart.php -->
<?php
include 'inc/head.inc.php';
include 'inc/nav.inc.php';
session_start(); // Start session to access cart
require 'db-connect.php';

// Handle item removal from cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_id"])) {
    $remove_id = (int) $_POST["remove_id"];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $remove_id) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
            break;
        }
    }
    header("Location: cart.php");
    exit();
}

// Calculate total price
$total_price = 0;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
}
?>

<div class="container mt-4">
    <h2>Your Cart</h2>
    
    <?php if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>1</td> <!-- Simple quantity handling for now -->
                        <td>
                            <form method="POST">
                                <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4>Total: $<?= number_format($total_price, 2) ?></h4>
        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
    <?php endif; ?>
</div>

<?php include 'inc/footer.inc.php'; ?>
