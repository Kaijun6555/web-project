<?php
session_start();

if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $_SESSION['user_location'] = [
        'lat' => $_POST['latitude'],
        'long' => $_POST['longitude']
    ];
    echo "Location saved successfully!";
} else {
    echo "Error: No location data received!";
}
?>