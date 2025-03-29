<?php
session_start();

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['latitude']) && isset($data['longitude']) && isset($data['address'])) {
    $_SESSION['user_location'] = [
        'lat' => $data['latitude'],
        'long' => $data['longitude'],
        'address' => $data['address']
    ];
    echo json_encode(["message" => "Location saved successfully!", "redirect" => "/user/restaurants.php"]);
} else {
    echo json_encode(["error" => "No location data received!"]);
}
?>