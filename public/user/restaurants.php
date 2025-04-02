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
    <style>
        .restaurant-card {
            transition: box-shadow 0.3s ease;
        }

        .restaurant-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .fixed-img {
            object-fit: cover;
            height: 180px;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .restaurant-name {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .restaurant-distance {
            font-size: 0.9rem;
            color: #6c757d;
        }

        a.card-link {
            text-decoration: none;
            color: inherit;
        }

        a.card-link:hover {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4 text-center">🍽️ Restaurants Near You</h2>
        <div class="row g-4">
            <?php
            $stmt = $conn->prepare("
                SELECT idrestaurant, name, address, image, 
                (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(lat)) *
                COS(RADIANS(`long`) - RADIANS(?)) + SIN(RADIANS(?)) *
                SIN(RADIANS(lat)))) AS distance
                FROM restaurant
                WHERE approval = 1
                ORDER BY distance ASC
            ");
            $stmt->bind_param("ddd", $user_lat, $user_lon, $user_lat);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()):
            ?>
                <div class="col-md-6 col-lg-4">
                    <a href="/user/restaurant.php?id=<?= $row['idrestaurant'] ?>" class="card-link">
                        <div class="card restaurant-card h-100 shadow-sm">
                            <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top fixed-img" alt="Restaurant Image">
                            <div class="card-body">
                                <p class="restaurant-name"><?= htmlspecialchars($row['name']) ?></p>
                                <p class="restaurant-distance"><?= number_format($row['distance'], 2) ?> km away</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>