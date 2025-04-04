<?php
require '../../db/db-connect.php';
session_start();

// Get JSON data from frontend
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$paypalEmail = $data['paypalEmail'];
$amount = $data['amount'];

// Save PayPal email only if not already set
$stmt = $conn->prepare("SELECT paypal_email FROM Users WHERE idUsers = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($existingEmail);
$stmt->fetch();
$stmt->close();

if (empty($existingEmail)) {
    $stmt = $conn->prepare("UPDATE Users SET paypal_email = ? WHERE idUsers = ?");
    $stmt->bind_param("si", $paypalEmail, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Your PayPal credentials (sandbox)
$clientID = 'AYX3VoAfHt6l59ysb2FMJejhy4yFe670slGGzQw9H7R5ezdH8yfGzAhdeX2rn9mrWER6YxQi9eKXi-3E';   
$secret = 'EFSfkijDCxoTV3a3sXgP6uc8qiwe9w94iVv1iFqtrT-2vmAPnizDT3QJmR6NoIuRzX4jNjpWiG3iKt38';  

// Step 1: Get PayPal Access Token
function getAccessToken($clientID, $secret)
{
    $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    $data = 'grant_type=client_credentials';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($clientID . ':' . $secret),
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    $responseObj = json_decode($response);

    curl_close($ch);

    return $responseObj->access_token;
}

// Step 2: Send Payment (Payout)
function sendPayout($accessToken, $recipientEmail, $amount)
{
    $url = 'https://api.sandbox.paypal.com/v1/payments/payouts';

    // Create the payout request
    $payoutData = [
        'sender_batch_header' => [
            'email_subject' => 'You have a payment',
            'recipient_type' => 'EMAIL'
        ],
        'items' => [
            [
                'recipient_wallet' => 'PAYPAL',
                'amount' => [
                    'value' => $amount,
                    'currency' => 'SGD'
                ],
                'note' => 'Payment for services',
                'receiver' => $recipientEmail
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payoutData));

    $response = curl_exec($ch);
    $responseObj = json_decode($response);

    curl_close($ch);

    return $responseObj;
}

// Step 3: Main Process
try {
    // Get the access token
    $accessToken = getAccessToken($clientID, $secret);

    // Send the payout to the recipient
    $payoutResponse = sendPayout($accessToken, $paypalEmail, $amount);

    // Step 4: Handle the response from PayPal
    if (isset($payoutResponse->batch_header->batch_status) && ($payoutResponse->batch_header->batch_status == 'PENDING')) {
        echo json_encode(['message' => $payoutResponse->batch_header->batch_status]);
    } else {
        // Check if there's an error in the response and extract error details
        if (isset($payoutResponse->error) && is_array($payoutResponse->error)) {
            $errorDetails = [];
            foreach ($payoutResponse->error as $error) {
                $errorDetails[] = [
                    'error_code' => $error->error_code,
                    'error_message' => $error->message,
                ];
            }
            // Return error details as part of the response
            echo json_encode([
                'message' => 'Payment failed. Please try again.',
                'error_details' => $errorDetails
            ]);
        } else {
            // General fallback error message if no specific error is found
            echo json_encode(['message' => "Payment failed. Please try again."]);
        }
    }

} catch (Exception $e) {
    // Handle any errors
    echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
}

?>