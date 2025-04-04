<!-- RESTUARANT login.php -->
<?php
session_start();
require '../../db/db-connect.php';

// Generate a CSRF token if not already generated
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // 32 bytes = 64 characters
}

// Check if the merchant is already logged in (adjust the session variable name as needed)
if (isset($_SESSION['restaurant_id'])) {
    header('Location: /restaurant/dashboard.php');  // Redirect to the merchant dashboard
    exit;
}

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Token is valid, process the form
        // Handle the form data here
    } else {
        // Invalid CSRF token, reject the request
        die("Invalid CSRF token.");
    }
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT idrestaurant, name, password FROM restaurant WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['restaurant_id'] = $id;
                $_SESSION['restaurant_name'] = $name;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with this email. <a href='/restaurant/restaurant_register.php'>Register Your Merchant Here!</a>";
        }
        $stmt->close();
    }
} ?>
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
            <h1>Merchant Login</h1>
            <?php if (isset($_GET['register_success'])): ?>
                <div class="alert alert-success">Registration successful! Please log in.</div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"> <?= $error ?> </div>
            <?php endif; ?>

            <?php if (isset($_GET['require_login'])): ?>
                <div class="alert alert-danger">Please Log In to Continue</div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email">Merchant Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>

                </div>
                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" id="password"name="password" class="form-control" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        <a href='/restaurant/restaurant_register.php'>Register Your Merchant Here!</a>
        </div>
    </main>
    <?php include '../inc/footer.inc.php'; ?>

</body>

</html>