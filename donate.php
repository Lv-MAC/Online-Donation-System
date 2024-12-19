<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get POST data
$event = isset($_POST['event']) ? $_POST['event'] : '';
$donationAmount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// Validate donation amount
if ($donationAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid donation amount']);
    exit();
}

// Connect to the database
$mysqli = new mysqli('localhost', 'root', '', 'donate');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get current total credit
$sql = "SELECT total_credit FROM total_credits WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentCredit = 0;
if ($credit = $result->fetch_assoc()) {
    $currentCredit = $credit['total_credit'];
}

// Check if the user has enough credit
if ($donationAmount > $currentCredit) {
    echo json_encode(['success' => false, 'message' => 'Insufficient credits']);
    exit();
}

// Deduct the credit and update the total credit balance
$newCredit = $currentCredit - $donationAmount;
$sql = "UPDATE total_credits SET total_credit = ? WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("di", $newCredit, $userId);
$stmt->execute();

// Record the donation in the donations table
$sql = "INSERT INTO donations (user_id, event, amount) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("isd", $userId, $event, $donationAmount);
$stmt->execute();

// Close the statement and database connection
$stmt->close();
$mysqli->close();

// Return success response
echo json_encode(['success' => true, 'message' => 'Donation successful!']);
?>
