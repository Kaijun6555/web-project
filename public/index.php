<?php
require '../db/db-connect.php';
session_start();

// Check for location
if (isset($_SESSION['user_location'])) {
    $user_lat = $_SESSION['user_location']['lat'];
    $user_lon = $_SESSION['user_location']['long'];
    $user_address = $_SESSION['user_location']['address'];
}

// Check if a user is logged in and if they have an incomplete order (status != 5)
$trackingOrderId = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT idOrders FROM Orders WHERE customer_user_id = ? AND status != 5 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $trackingOrderId = $row['idOrders'];
        // Store the order ID in session for tracking_order.php to retrieve
        $_SESSION['tracking_order'] = $trackingOrderId;
    }
    $stmt->close();
}
// Determine the tracking URL:
$trackingUrl = "/user/track_order.php";
if (isset($_SESSION['payerUrl']) && !empty($_SESSION['payerUrl'])) {
    $trackingUrl = $_SESSION['payerUrl'];
}

// Handle search query
$search = "";
if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Food</title>
    <?php include 'inc/head.inc.php'; ?>
    <link rel="stylesheet" href="/css/index.css">
</head>
<body>
    <?php include 'inc/nav.inc.php'; ?>
    <main class="container">
        <!-- Display Resume Tracking Button if an incomplete order exists -->
        <?php if ($trackingOrderId): ?>
            <div class="alert alert-info mt-3">
                You have an ongoing order.
                <a href="<?= htmlspecialchars($trackingUrl) ?>" class="btn btn-warning ms-2">Resume Tracking</a>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['require_login'])): ?>
            <div class="alert alert-danger">Please Log In to Continue</div>
        <?php endif; ?>

        <!-- Enter Location Section -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mt-5 border border-5 rounded-5">
                    <div class="card-body">
                        <h1 class="card-title">Greetings Foodie!</h1>
                        <h2 class="card-subtitle mb-2 text-muted">Where are you looking to deliver your food to?</h2>
                        <br>
                        <div class="form-group">
                            <input type="text" class="form-control" id="location" name="location"
                                placeholder="Type an Address" onfocus="getUserLocation()" oninput="toggleSubmitButton()"
                                <?php if (isset($_SESSION['user_location'])): ?> 
                                    value="<?= htmlspecialchars($user_address) ?>" <?php endif; ?>>
                            <br>
                            <?php if (isset($_GET['require_location'])): ?>
                                <div class="alert alert-danger">Please enter a location first</div>
                            <?php endif; ?>
                            <br>
                            <button class="btn w-100 text-muted background-orange" id="location-button" onclick="sendToServer()">
                                <strong>Search Restaurants</strong>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <img src="/static/main-image2.png" class="mt-5 d-none d-md-block" alt="hero image" width="100%">
            </div>
        </div>

        <hr>
        <!-- Search Bar for Restaurants -->
        <div class="row my-3">
            <div class="col-md-12">
                <form class="d-flex" method="get" action="">
                    <input class="form-control me-2" type="search" placeholder="Search Restaurants by Name or First Letter" aria-label="Search" name="q" value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>

        <h2>
            <a class="text-decoration-none text-muted" href="/user/restaurants.php">Restaurants Near You</a>
        </h2>
        <div class="row background-orange border rounded-4">
            <?php
            // Prepare SQL to fetch restaurants, with a search filter if provided
            $query = "SELECT idrestaurant, name, address, image FROM restaurant WHERE approval = 1";
            $params = [];
            $types = "";
            if (!empty($search)) {
                $query .= " AND name LIKE ?";
                $params[] = $search . '%';
                $types .= "s";
            }
            $query .= " ORDER BY name";
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()):
                ?>
                <div class="col-md-3 mt-3 mb-3 d-flex">
                    <a class="text-decoration-none w-100" href="/user/restaurant.php?id=<?= $row['idrestaurant'] ?>">
                        <div class="card restaurant h-100 d-flex flex-column">
                            <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top fixed-img" alt="store image">
                            <div class="card-body d-flex flex-column">
                                <h3 class="card-title flex-grow-1"><?= htmlspecialchars($row['name']) ?></h3>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <script>
            // Enable or disable the location button based on input value
            if(document.getElementById("location").value){
                document.getElementById("location-button").disabled = false;
            } else {
                document.getElementById("location-button").disabled = true;
            }
        </script>
    </main>
    <?php include 'inc/footer.inc.php'; ?>
</body>
</html>
