<?php
$email = $lname = $fname = $password = $errorMsg = "";

$success = true;
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
}

if (empty($_POST["pwd"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
}
if ($success) {
    $email = sanitize_input($_POST["email"]);
    $password = $_POST["pwd"];

    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.";
        $success = false;
    }
}

authenticateUser();

include "inc/head.inc.php";
include "inc/nav.inc.php";
echo '<div class="container"><div class="row justify-content-center"><div class="col-6">';
if ($success) {

    echo "<hr><h1>Login Successful!</h1>";
    echo "<h2>Welcome Back " . $fname . " " . $lname . "</h2>";
    echo '<button class="btn btn-success mb-3" type="submit"><a class="return_button" href="index.php">Return to Home</a></button>';
} else {
    echo '<hr>';
    echo "<h1>OOPS!<br>The following input errors were detected:</h1>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<button class="btn btn-warning mb-3" type="submit"><a class="return_button" href="login.php">Return To Login</a></button>';

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
 * Helper function to authenticate the member
 */
function authenticateUser()
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
            $stmt = $conn->prepare("SELECT * FROM world_of_pets_members WHERE email=?");
            // Bind & execute the query statement:
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Note that email field is unique, so should only have one row.
                $row = $result->fetch_assoc();
                $fname = $row["fname"];
                $lname = $row["lname"];
                $pwd_hashed = $row["password"];
                // Check if the password matches:
                if (!password_verify($_POST["pwd"], $pwd_hashed)) {
                    // Don’t tell hackers which one was wrong, keep them guessing...
                    $errorMsg = "Email not found or password doesn't match...";
                    $success = false;
                }
            } else {
                $errorMsg = "Email not found or password doesn't match...";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

?>