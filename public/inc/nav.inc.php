<?php
session_start();
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
    header("Location: /");
    exit();
}
?>



<nav class="background-orange navbar navbar-expand-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">
            <img src="/static/logo.png" alt="FoodFindr" width="221" height="50" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="restaurants.php">Restaurants</a>
                </li>


                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/user_orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/restaurant/restaurant_login.php">Merchant Centre</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#cart">
                        <i class="bi bi-cart"></i>
                    </button>
                </li>

                <li class="nav-item">
                    <button class="btn btn-primary"><i class="bi bi-bell"></i></button>
                </li>

                <li class="nav-item dropdown">
                    <button class="btn btn-primary dropdown-toggle" id="user_role_dropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Order Food
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="user_role_dropdown">
                        <li><a class="dropdown-item" href="/" onclick="changeButtonText('Order Food')">Order Food</a>
                        </li>
                        <li><a class="dropdown-item" href="/user/deliverer.php"
                                onclick="changeButtonText('Deliverer')">Be a Deliverer</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Cart Slider In Bar -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cart">
    <div class="offcanvas-header">
        <h2>My Cart</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
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
            <?php // Calculate total price
                $total_price = 0;
                if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                    foreach ($_SESSION['cart'] as $item) {
                        $total_price += $item['price'] * $item['quantity'];
                    }
                } 
            ?>
            <h4>Total: $<?= number_format($total_price, 2) ?></h4>
            <a href="/user/checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        <?php endif; ?>
    </div>
</div>