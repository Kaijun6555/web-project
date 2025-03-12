<?php
$email = $lname = $fname= $password = $errorMsg = "";

$success = true;
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
}
if (empty($_POST["lname"])) {
    $errorMsg .= "Last Name is required.<br>";
    $success = false;
}
if (empty($_POST["pwd"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
}
if ($success) {
    
    $email = sanitize_input($_POST["email"]);
    $password = $_POST["pwd"];
    $confirm_password = $_POST["pwd_confirm"];
    $lname = sanitize_input($_POST["lname"]);
    $fname = sanitize_input($_POST["fname"]);

    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.";
        $success = false;
    } elseif (!($password == $confirm_password)) {
        $errorMsg .= "Passwords Don't Match";
        $success = false;
    }

}
include "inc/head.inc.php";
include "inc/nav.inc.php";
echo '<div class="container"><div class="row justify-content-center"><div class="col-6">';
if ($success) {
    $pwd_hashed = password_hash($password, PASSWORD_DEFAULT);
    saveMemberToDB();
    echo "<hr><h1>Your Registration is successful!</h1>";
    echo "<h2>Thank You for Signing Up with Scott Jones Enterprises</h2>";
    echo '<button class="btn btn-success mb-3" type="submit"><a class="return_button" href="index.php">Log In</a></button>';
} else {
    echo '<hr>';
    echo "<h1>OOPS!<br>The following input errors were detected:</h1>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<button class="btn btn-danger mb-3" type="submit"><a class="return_button" href="register.php">Return To Sign Up</a></button>';

}
include "inc/footer.inc.php";
echo '</div>';
echo '</div></div>';


/*
 * Helper function that checks input for malicious or unwanted content.
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/*
 * Helper function to write the member data to the database.
 */
function saveMemberToDB()
{
    global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO world_of_pets_members
(fname, lname, email, password) VALUES (?, ?, ?, ?)");
            // Bind & execute the query statement:
            $stmt->bind_param("ssss", $fname, $lname, $email, $pwd_hashed);
            if (!$stmt->execute()) {
                $errorMsg = "Execute failed: (" . $stmt->errno . ") " .
                    $stmt->error;
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}


?>