<?php
session_start();
require '../../db/db-connect.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /user/login.php");
    exit();
}

// Check if a file parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    echo "No file specified.";
    exit();
}

// Sanitize the input to prevent directory traversal
$filename = basename($_GET['file']);

// Define the directory where verification files are stored.
// Adjust the path as needed to match your server's file structure.
$verificationDir = realpath(__DIR__ . '/../restaurant/verification');
$filepath = $verificationDir . DIRECTORY_SEPARATOR . $filename;

// Verify the file exists and is inside the verification directory
if (!file_exists($filepath) || strpos(realpath($filepath), $verificationDir) !== 0) {
    echo "File not found.";
    exit();
}

// Determine MIME type based on file extension
$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
switch ($ext) {
    case 'pdf':
        $mime = 'application/pdf';
        break;
    case 'doc':
        $mime = 'application/msword';
        break;
    case 'docx':
        $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;
    default:
        $mime = 'application/octet-stream';
        break;
}

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit();
?>
