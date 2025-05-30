<?php
$ORDER_PREPARED_STATUS=3;
session_start();
require '../../db/db-connect.php';

if (isset($_POST['order_id']) && isset($_SESSION['restaurant_id'])) {

    $order_id = $_POST['order_id'];
    $restaurant_id = $_SESSION['restaurant_id'];
     
    // Prepare the SQL update statement
    $stmt = $conn->prepare("UPDATE Orders SET status = ? WHERE idOrders = ? AND restaurant_id = ?");
    $stmt->bind_param("ddi",$ORDER_PREPARED_STATUS, $order_id, $restaurant_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?>