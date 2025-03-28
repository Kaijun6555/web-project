<?php
$DELIVERY_COMPLETE=5;
session_start();
require '../../db/db-connect.php';

if (isset($_POST['order_id']) && isset($_SESSION['user_id'])) {

    $order_id = $_POST['order_id'];
    $delivery_user_id = $_SESSION['user_id'];
     
    // Prepare the SQL update statement
    $stmt = $conn->prepare("UPDATE Orders SET status = ? WHERE idOrders = ? AND delivery_user_id = ?");
    $stmt->bind_param("iii",$DELIVERY_COMPLETE, $order_id, $delivery_user_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?>