<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include 'inc/head.inc.php'; ?>
</head>

<body>
    <?php include 'inc/nav.inc.php'; ?>
    <main class="container">
        <!-- Enter Location -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mt-5 border border-5 rounded-5">
                    <div class="card-body">
                        <h4 class="card-title">Greetings Foodie!</h4>
                        <h2 class="card-subtitle mb-2 text-muted">Where are you looking to deliver your food to?</h2>
                        <br>
                        <div class="form-group">
                            <!-- <label for="location">Enter Location</label> -->
                            <input type="text" class="form-control" id="location" name="location"
                                placeholder="Type an Address" onfocus="getLocation()">
                            <br>
                            <button class="btn w-100 text-muted background-orange" type="submit">
                                <strong>Confirm Location</strong>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <img src="/static/main-image2.png" class="mt-5" alt="hero image" width="100%">
            </div>
        </div>
        <hr>
        <h2>
            <a class="text-decoration-none text-muted" href="/user/restaurants.php">Restaurants Near You
            </a>
        </h2>
        <div class="row background-orange border rounded-4">
            <?php
            require '../db/db-connect.php';
            $stmt = $conn->prepare("SELECT idrestaurant, name, address,image FROM restaurant ORDER BY name");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()):
                ?>
                <div class="col-md-3 mt-3 mb-3">
                    <a class="text-decoration-none" href="/user/restaurant.php?id=<?= $row['idrestaurant'] ?>">
                        <div class="card restaurant">
                            <img src="<?= $row['image'] ?>" class="card-img-top" alt="store image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(sendPosition, showError);
            } else {
                document.getElementById("output").innerText = "Geolocation is not supported by this browser.";
            }
        }

        function sendPosition(position) {
            let latitude = position.coords.latitude;
            let longitude = position.coords.longitude;
            document.getElementById("location").value = latitude + ", " + longitude;
        }

        function showError(error) {
            document.getElementById("location").value = "Can't Get Location";
        }
    </script>
</body>

</html>