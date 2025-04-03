<?php
session_start();
require '../../db/db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login.php?require_login=true");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

// Fetch current user info
$stmt = $conn->prepare("SELECT name, email, paypal_email, preferences FROM Users WHERE idUsers = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $paypal_email, $preferences);
$stmt->fetch();
$stmt->close();

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = sanitize_input($_POST['name']);
    $new_email = sanitize_input($_POST['email']);
    $new_password = $_POST['password'];
    $new_paypal = sanitize_input($_POST['paypal_email']);

    if (empty($new_name) || empty($new_email)) {
        $error = "Name and Email are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($new_paypal) && !filter_var($new_paypal, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid PayPal email format.";
    } else {
        // Check if the email is already taken by another user
        $stmt = $conn->prepare("SELECT idUsers FROM Users WHERE email = ? AND idUsers != ?");
        $stmt->bind_param("si", $new_email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "This email is already taken.";
        } else {
            // Update user info
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, password = ?, paypal_email = ? WHERE idUsers = ?");
                $stmt->bind_param("ssssi", $new_name, $new_email, $hashed_password, $new_paypal, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE Users SET name = ?, email = ?, paypal_email = ? WHERE idUsers = ?");
                $stmt->bind_param("sssi", $new_name, $new_email, $new_paypal, $user_id);
            }

            if ($stmt->execute()) {
                $_SESSION['user_name'] = $new_name;
                $success = "Profile updated successfully.";
                $name = $new_name;
                $email = $new_email;
                $paypal_email = $new_paypal;
            } else {
                $error = "Something went wrong. Try again.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Settings</title>
    <?php include '../inc/head.inc.php'; ?>
</head>
<body>
<?php include '../inc/nav.inc.php'; ?>

<div class="container mt-4">
    <h2>User Settings</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
        </div>
        <div class="mb-3">
            <label>New Password <small>(leave blank to keep current)</small></label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label>PayPal Email</label>
            <input type="email" name="paypal_email" class="form-control" value="<?= htmlspecialchars($paypal_email) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<?php include '../inc/footer.inc.php'; ?>
</body>
</html>
