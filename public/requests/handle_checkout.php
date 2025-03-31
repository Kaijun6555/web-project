<?php
session_start();
require '../../db/db-connect.php';

$ORDER_ONGOING = 1;

if (isset($_POST['user_id'])) {

    $user_id = $_POST['user_id'];

    // Prepare the SQL update statement
    $stmt = $conn->prepare("UPDATE Users SET order_ongoing = ? WHERE idUsers = ?");
    $stmt->bind_param("ii", $ORDER_ONGOING, $user_id);
    $stmt->execute();
    $stmt->close();
    echo "SUCCESS";
    
} else {
    echo "Error: Cannot update order ongoing field";
}
?>