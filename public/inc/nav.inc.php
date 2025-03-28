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

<nav class="background-orange navbar navbar-expand-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">
            <img src="/static/logo.png" alt="FoodFinder" width="221" height="50" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <?php if (empty($_SESSION['user_id'])): ?>
                    <li class="nav-item me-3">
                        <button class="btn navbar-button">
                            <a class="text-black text-decoration-none" href="/restaurant/restaurant_login.php">
                                Merchant Centre</a>
                        </button>
                    </li>
                <?php endif; ?>

                <li class="nav-item me-3">
                    <button class="btn navbar-button" data-bs-toggle="offcanvas" data-bs-target="#cart">
                        <a class="text-black text-decoration-none">
                            <i class="bi bi-cart"></i>&nbsp;Cart
                        </a>
                    </button>
                </li>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="/user/user_orders.php">My Orders</a>
                    </li> -->

                    <li class="nav-item dropdown me-3">
                        <button class="btn navbar-button dropdown-toggle text-black" id="user_role_dropdown" role="button"
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
                    <li class="nav-item me-3">
                        <button class="btn btn-danger">
                            <a class="text-white text-decoration-none" href="/logout.php">
                                Logout</a>
                        </button>
                    </li>
                <?php else: ?>
                    <li class="nav-item me-3">
                        <button class="btn navbar-button">
                            <a class="text-black text-decoration-none" href="/user/login.php"><i
                                    class="bi bi-person-circle"></i>&nbsp;Login/Register</a>
                        </button>
                    </li>
                <?php endif; ?>
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