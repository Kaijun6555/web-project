<?php
require '../../db/db-connect.php';
session_start();

if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_lon = $_SESSION['user_location']['long'];
    $user_address = $_SESSION['user_location']['address'];
} else {
    header("Location: /?require_location=1");
    exit();
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
            <?php
            $stmt = $conn->prepare("SELECT idrestaurant, name, address, image FROM restaurant WHERE approval = 1 ORDER BY name");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()):
                ?>
                <div class="col-md-3 mt-3 mb-3 d-flex">
                    <a class="text-decoration-none w-100" href="/user/restaurant.php?id=<?= $row['idrestaurant'] ?>">
                        <div class="card restaurant h-100 d-flex flex-column">
                            <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top fixed-img"
                                alt="store image">
                            <a href="/user/restaurant.php?id=<?= htmlspecialchars($row['idrestaurant']) ?>">
                                <?= htmlspecialchars($row['name']) ?> - <?= number_format($row['distance'], 2) ?> km away
                            </a>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </ul>
    </div>

    <?php include '../inc/footer.inc.php'; ?>
    <script>
    </script>
</body>

</html>