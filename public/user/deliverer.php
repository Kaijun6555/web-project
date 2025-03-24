<!DOCTYPE html>
<html lang="en">
<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>
<body>

    <?php include '../inc/nav.inc.php'; ?>

    <main class="container"> 

        <h2>Nearby Deliver Requests</h2>

        <!-- Display Map Or List -->
       
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
            document.getElementById("location").value = -35.249029 + ", " + 148.977341;
        }
    </script>
</body>

</html>