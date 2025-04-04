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
    $address = sanitize_input($_POST['location']);

    $lon = sanitize_input($_POST['restaurant_lon']);
    $lat = sanitize_input($_POST['restaurant_lat']);

    $verification_file_tmp_path = $_FILES['verification']['tmp_name'];
    $verification_file_name = $_FILES['verification']['name'];
    $verification_file_size = $_FILES['verification']['size'];

    $verification_image_file_tmp_path = $_FILES['verification_image']['tmp_name'];
    $verification_image_file_name = $_FILES['verification_image']['name'];
    $verification_image_file_size = $_FILES['verification_image']['size'];

    $target_directory = "verification/";

    $verification_image_file_path = $target_directory . basename($verification_image_file_name);
    $verification_file_path = $target_directory . basename($verification_file_name);
    $verification_image_file_type = strtolower(pathinfo($verification_image_file_path, PATHINFO_EXTENSION));
    $verification_file_type = strtolower(pathinfo($verification_file_path, PATHINFO_EXTENSION));
    if ($verification_image_file_size > 300000) {
        echo "Sorry, your image file is too large";
        exit();
    }
    if (
        $verification_image_file_type != "jpg" && $verification_image_file_type != "png" && $verification_image_file_type != "jpeg"
    ) {
        echo "Sorry, only JPG, JPEG, PNG files are allowed.";
        exit();
    }
    if (
        $verification_file_type != "pdf" && $verification_file_type != "doc"
    ) {
        echo "Sorry, only pdf, doc files are allowed.";
        exit();
    }
    if (!is_dir($target_directory)) {
        mkdir($target_directory, 0777, true);
    }
    if (is_dir($target_directory)) {
        chmod($target_directory, 0775);
    }
    if (!move_uploaded_file($verification_image_file_tmp_path, $verification_image_file_path) && !move_uploaded_file($verification_file_tmp_path, $verification_file_path)) {
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
            $stmt->bind_param("ssssss", $name, $email, $address, $verification_image_file_path, $verification_file_path, $hashed_password, $lon, $lat);

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
    <div class="container mt-4">
        <h2>Register as a Merchant With Us!</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?= $error ?> </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Merchant Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Merchant Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Merchant Address</label>
                <input type="text" name="location" class="form-control" oninput="handleInput(event)" required>
            </div>
            <div class="mb-3">
                <label>Please Provide Link to an image </label>
                <input type="text" name="image" class="form-control">
            </div>
            <div class="mb-3">
                <label>Verification: Please Submit an .pdf or .doc file</label>
                <input type="file" name="verification" class="form-control">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <input type="hidden" name="restaurant_lon" class="form-control" required>
            <input type="hidden" name="restaurant_lat" class="form-control" required>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>