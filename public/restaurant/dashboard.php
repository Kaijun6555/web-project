<?php
require '../../db/db-connect.php';
session_start();

if (!$_SESSION['restaurant_id'] == null) {
    $restaurant_id = $_SESSION['restaurant_id'];
}
else {
    die("Not Logged in");
}

$stmt = $conn->prepare("SELECT idmenu_item, itemName, price, availability, description, image FROM menu_item WHERE restaurant_id = ? ORDER BY itemName") ;
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
        <div class="row">
            <div class="col min-vh-100 py-3">
                <!-- toggler -->
                <button class="btn float-end" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" role="button">
                    <i class="bi bi-arrow-right-square-fill fs-3" data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvas"></i>
                </button>
                Dashboard Sales
                

            </div>
        </div>
    </div>
    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>