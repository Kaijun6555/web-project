<?php
session_start();
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
                        <a class="nav-link" href="user_orders.php">My Orders</a>
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
                    <button class="btn btn-primary"><i class="bi bi-cart"></i></button>

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
                        <li><a class="dropdown-item" href="/user/deliverer.php" onclick="changeButtonText('Deliverer')">Be a Deliverer</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>