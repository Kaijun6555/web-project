<?php
session_start();
require '../../db/db-connect.php';

if (isset($_POST['latitude']) && isset($_POST['longitude']) && isset($_POST['order_id'])) {

    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    $order_id = $_POST['order_id'];
    
    // Prepare the SQL update statement
    $stmt = $conn->prepare("UPDATE Orders SET delivery_long = ?, delivery_lat = ? WHERE idOrders = ?");
    $stmt->bind_param("ddi", $longitude, $latitude, $order_id);
    $stmt->execute();
    $stmt->close();
    echo "Location updated successfully to $longitude, $latitude";
    
} else {
    echo "Error: No location data received!";
}
?>