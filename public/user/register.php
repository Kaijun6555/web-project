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
        // First, check if the email exists in the admin table
        $stmt = $conn->prepare("SELECT admin_id FROM Admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $stmt->close();

            // Then, check if the email exists in the Users table
            $stmt = $conn->prepare("SELECT idUsers FROM Users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Email is already registered.";
            } else {
                $stmt->close();

                // Hash password using a secure algorithm
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO Users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);

                if ($stmt->execute()) {
                    header("Location: login.php?register_success=1");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
        $stmt->close();
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
    <main>
        <div class="container mt-4">
            <h1>Register</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    
                </div>
                <div class="mb-3">
                    <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    
                </div>
                <div class="mb-3">
                    <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
        </div>
    </main>
    <?php include '../inc/footer.inc.php'; ?>
</body>

</html>