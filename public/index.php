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

                <div class="card mt-5">
                    <div class="card-body">
                        <h4 class="card-title">Greetings Foodie!</h4>
                        <h2 class="card-subtitle mb-2 text-muted">Where are you looking to deliver your food to?</h2>
                        <br>
                        <div class="form-group">
                            <!-- <label for="location">Enter Location</label> -->
                            <input type="text" class="form-control" id="location" name="location"
                                placeholder="Type an Address" onfocus="getLocation()">
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <hr>
        <h2>Restaurants</h2>
        <div class="row">
            <?php
            require '../db/db-connect.php';
            $stmt = $conn->prepare("SELECT idrestaurant, name, address FROM restaurant ORDER BY name");
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()):
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                            <a href="/user/restaurant.php?id=<?= $row['idrestaurant'] ?>" class="btn btn-primary">View Menu</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
    <?php include '../inc/footer.inc.php'; ?>

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