<?php
require '../../db/db-connect.php';

if (isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);
    $stmt = $conn->prepare("SELECT status, delivery_long, delivery_lat FROM Orders WHERE idOrders = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($row = $result->fetch_assoc()) {
        $status = "Unset";
        if ($row['status'] == 1){
            $status = "Looking for Deliverer";
        }
        else if ($row['status'] == 2){
            $status = "Order is being Prepared";
        }
        else if ($row['status'] == 3){
            $status = "Deliverer is picking up the order";
        }
        else if ($row['status'] == 4){
            $status = "Deliverer is on the way";
        }
        else if ($row['status'] == 5){
            $status = "Order is delivered";
        } 
        
        echo json_encode([
            "status" => $status,
            "delivery_long" => $row['delivery_long'],
            "delivery_lat" => $row['delivery_lat']
        ]);
    } else {
        echo json_encode(["status" => "Not Found"]);
    }
} else {
    header("Location: /");
    exit();
}
?>