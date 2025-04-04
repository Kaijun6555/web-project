<!-- RESTAURANT register.php -->
<?php
require '../../db/db-connect.php';

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $openinghours = sanitize_input($_POST['opening_hours']);
    $address = sanitize_input($_POST['location']);

    $lon = sanitize_input($_POST['restaurant_lng']);
    $lat = sanitize_input($_POST['restaurant_lat']);

    $verification_file_tmp_path = $_FILES['verification']['tmp_name'];
    $verification_file_name = $_FILES['verification']['name'];
    $verification_file_size = $_FILES['verification']['size'];

    $target_directory = "verification/";

    $image = $_POST['image'];
    $image_data = getimagesize($image);

    $verification_file_path = $target_directory . basename($verification_file_name);
    $verification_file_type = strtolower(pathinfo($verification_file_path, PATHINFO_EXTENSION));
    if ($verification_file_size > 300000) {
        echo "Sorry, your doc or pdf file is too large";
        exit();
    }
    if (!strpos($image_data['mime'], 'image/') === 0) {
        header("Location: /restaurant/restaurant_register.php");
        echo "
        Provide a link to an image file online.
        ";
    }
    if (
        $verification_file_type != "pdf" && $verification_file_type != "docx"
    ) {
        echo "Sorry, only pdf, doc files are allowed.";
        exit();
    }
    if (!is_dir($target_directory)) {
        mkdir($target_directory, 0775, true);
    }
    if (!move_uploaded_file($verification_file_tmp_path, $verification_file_path)) {
        echo "Sorry, there was an error uploading your file.";
        exit();
    }
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT idrestaurant FROM restaurant WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO restaurant (name, email, address, image, verification, password, `long`, lat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssdd", $name, $email, $address, $image, $verification_file_path, $hashed_password, $lon, $lat);

            if ($stmt->execute()) {
                header("Location: restaurant_login.php?register_success=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!-- RESTUARANT register.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>
    <main>
        <div class="container mt-4">
            <h1>Register as a Merchant With Us!</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"> <?= $error ?> </div>
            <?php endif; ?>
            <form method="POST" id="merchant_form" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name">Merchant Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email">Merchant Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="location">Merchant Address</label>
                    <input type="text" name="location" id="location" class="form-control" oninput="handleInput(event)" required>
                </div>
                <div class="mb-3">
                    <label for="opening_hours">Merchant Opening Hours</label>
                    <input type="text" name="opening_hours" id="opening_hours" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="image">Please Provide a link to an image </label>
                    <input type="text" name="image" id="image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="verification">Verification: Please Submit an .pdf or .doc file</label>
                    <input type="file" name="verification" id="verification" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>

                <input type="hidden" name="restaurant_lng" id="restaurant_lng"  required>
                <input type="hidden" name="restaurant_lat" id="restaurant_lat"  required>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>
        </div>
    </main>
    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>