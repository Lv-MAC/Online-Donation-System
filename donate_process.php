<?php
// Start session and include database connection
session_start();
include('db_connection.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Extract variables from JSON input
    $userId = $input['user_id'];
    $eventId = $input['event_id'];
    $amount = $input['amount'];

    // Validate inputs
    if (empty($userId) || empty($eventId) || empty($amount) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit();
    }

    // Check if the user has enough credit
    $sql = "SELECT total_credit FROM total_credits WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $credit = $result->fetch_assoc();

    if (!$credit || $credit['total_credit'] < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient credit.']);
        exit();
    }

    // Deduct the donation amount from the user's total credit
    $newCredit = $credit['total_credit'] - $amount;
    $sql = "UPDATE total_credits SET total_credit = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $newCredit, $userId);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update credit.']);
        exit();
    }

    // Record the donation in the donations table
    $sql = "INSERT INTO donations (user_id, event_id, amount, donated_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iid", $userId, $eventId, $amount);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Donation successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record donation.']);
    }

    exit();
}
