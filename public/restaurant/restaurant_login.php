<!-- RESTUARANT login.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Food</title>
    <?php include '../inc/head.inc.php'; ?>
</head>

<body>
    <?php include '../inc/nav.inc.php'; ?>

    <?php
    session_start();
    require '../../db/db-connect.php';

    function sanitize_input($data)
    {
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
    }
    ?>

    <div class="container mt-4">
        <h2>Merchant Login</h2>
        <?php if (isset($_GET['register_success'])): ?>
            <div class="alert alert-success">Registration successful! Please log in.</div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?= $error ?> </div>
        <?php endif; ?>
            <>
        <form method="POST">
            <div class="mb-3">
                <label>Merchant Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <?php include '../inc/footer.inc.php'; ?>

</body>

</html>