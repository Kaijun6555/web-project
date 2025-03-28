<?php
session_start();
require '../../db/db-connect.php';

// Check if the user or admin is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');  // Redirect to the homepage or user dashboard
    exit;
}
if (isset($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');  // Redirect to the admin dashboard
    exit;
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // First, check if the admin exists with this email.
        $stmt = $conn->prepare("SELECT admin_id, admin_password FROM Admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $admin_password);
            $stmt->fetch();
            // For admins, compare MD5-hashed password
            if (md5($password) === $admin_password) {
                $_SESSION['admin_id'] = $admin_id;

                header("Location: /admin/dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
            $stmt->close();
        } else {
            // If not found in admin table, check the Users table
            $stmt = $conn->prepare("SELECT idUsers, name, password FROM Users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $name, $hashed_password);
                $stmt->fetch();
                // Verify user password using password_verify
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    header("Location: /index.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "No account found with this email.";
            }
            $stmt->close();
        }
    }
}
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
        <h2>Login</h2>
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
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <a href="/user/register.php">Don't have an account? Sign up Here!</a>
    </div>

    <?php include '../inc/footer.inc.php'; ?>
</body>
</html>
