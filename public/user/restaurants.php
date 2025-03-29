<?php
require '../../db/db-connect.php';
session_start();

if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_lon = $_SESSION['user_location']['long'];
    $user_address = $_SESSION['user_location']['address'];
}

// Query to get restaurants sorted by nearest distance
$query = "
    SELECT idrestaurant, name, address, lat, `long`,
    (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(lat)) * 
    COS(RADIANS(`long`) - RADIANS(?)) + SIN(RADIANS(?)) * 
    SIN(RADIANS(lat)))) AS distance
    FROM restaurant
    ORDER BY distance ASC";


$stmt = $conn->prepare($query);
$stmt->bind_param("ddd", $user_lat, $user_lon, $user_lat);
$stmt->execute();
$result = $stmt->get_result();
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
        <h2>Restaurants Near You</h2>
        <ul class="list-group">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <a href="/user/restaurant.php?id=<?= htmlspecialchars($row['idrestaurant']) ?>">
                        <?= htmlspecialchars($row['name']) ?> - <?= number_format($row['distance'], 2) ?> km away
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <?php include '../inc/footer.inc.php'; ?>
    <script>
    </script>
</body>

</html>