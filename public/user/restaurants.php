<?php
include '../inc/head.inc.php';
include '../inc/nav.inc.php';
require '../../db/db-connect.php';

// Get user's IP-based location using `ip-api.com`
$user_lat = 0;
$user_lon = 0;
$ip_data = @json_decode(file_get_contents("http://ip-api.com/json/?fields=lat,lon"), true);
if ($ip_data && isset($ip_data['lat']) && isset($ip_data['lon'])) {
    $user_lat = $ip_data['lat'];
    $user_lon = $ip_data['lon'];
}

// Query to get restaurants sorted by nearest distance
$query = "
    SELECT id, name, address, latitude, longitude,
    (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * 
    COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * 
    SIN(RADIANS(latitude)))) AS distance
    FROM restaurants
    ORDER BY distance ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ddd", $user_lat, $user_lon, $user_lat);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>Restaurants Near You</h2>
    <ul class="list-group">
        <?php while ($row = $result->fetch_assoc()): ?>
            <li class="list-group-item">
                <a href="/user/restaurant.php?id=<?= htmlspecialchars($row['id']) ?>">
                    <?= htmlspecialchars($row['name']) ?> - <?= number_format($row['distance'], 2) ?> km away
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
</div>

<?php include '../inc/footer.inc.php'; ?>
