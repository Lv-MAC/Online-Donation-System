<?php
session_start();
include('db_connection.php');  // Include database connection

// Ensure the user is logged in by checking the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id'];  // Get the user ID from the session

// Check if the form is submitted and process the credit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];  // Amount of credit to be added
    $userId = $_POST['user_id']; // User ID

    // Insert into the `credits` table
    $creditQuery = "INSERT INTO credits (user_id, amount, date) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($creditQuery);
    $stmt->bind_param("ii", $userId, $amount);
    if ($stmt->execute()) {
        // Update the `total_credits` table
        $totalCreditQuery = "SELECT total_credit FROM total_credits WHERE user_id = ?";
        $stmt = $conn->prepare($totalCreditQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // If the user already has a total credit record, update it
            $totalCreditRow = $result->fetch_assoc();
            $newTotalCredit = $totalCreditRow['total_credit'] + $amount;
            $updateQuery = "UPDATE total_credits SET total_credit = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ii", $newTotalCredit, $userId);
            $stmt->execute();
        } else {
            // If no total credit record exists, create one
            $insertQuery = "INSERT INTO total_credits (user_id, total_credit) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii", $userId, $amount);
            $stmt->execute();
        }
        // Redirect back to the dashboard after adding credit
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error adding credit.";
    }
}
?>
