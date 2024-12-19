<?php
// session_start();
// include('db_connection.php');

// // Ensure the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// $userId = $_SESSION['user_id'];  // Get the logged-in user ID
// $amount = $_POST['amount'];  // Get the amount from the form

// // Fetch current credit balance
// $query = "SELECT credit_balance FROM users WHERE user_id = ?";
// $stmt = $conn->prepare($query);
// $stmt->bind_param("i", $userId);
// $stmt->execute();
// $result = $stmt->get_result();
// $user = $result->fetch_assoc();

// if ($user) {
//     $currentBalance = $user['credit_balance'];
//     $newBalance = $currentBalance + $amount;  // Add the new credit to the existing balance

//     // Update the user's credit balance in the database
//     $updateQuery = "UPDATE users SET credit_balance = ? WHERE user_id = ?";
//     $stmt = $conn->prepare($updateQuery);
//     $stmt->bind_param("di", $newBalance, $userId);

//     if ($stmt->execute()) {
//         // Redirect back to the dashboard to reflect the updated credit balance
//         header("Location: dashboard.php");
//         exit();
//     } else {
//         echo "Error updating credit balance.";
//     }
// } else {
//     echo "User not found.";
// }


?>

<?php
// session_start();
// include('db_connection.php');

// // Read JSON input
// $data = json_decode(file_get_contents('php://input'), true);

// if (isset($data['user_id'], $data['event_id'], $data['amount'])) {
//     $userId = $data['user_id'];
//     $amount = $data['amount'];

//     // Update total credit
//     $query = "UPDATE total_credits SET total_credit = total_credit - ? WHERE user_id = ?";
//     $stmt = $conn->prepare($query);
//     $stmt->bind_param("di", $amount, $userId);

//     if ($stmt->execute()) {
//         echo json_encode(["success" => true]);
//     } else {
//         echo json_encode(["success" => false, "error" => $conn->error]);
//     }
// } else {
//     echo json_encode(["success" => false, "error" => "Invalid input"]);
// }
?>

<?php
session_start();
include('db_connection.php'); // Include database connection

// Read JSON input from the frontend
$data = json_decode(file_get_contents('php://input'), true);

// Check if required fields are present
if (isset($data['user_id'], $data['event_id'], $data['amount'])) {
    $userId = $data['user_id'];
    $eventId = $data['event_id'];
    $amount = $data['amount'];
    $date = date('Y-m-d H:i:s'); // Current date and time

    // Update the total credit in the database
    $updateCreditQuery = "UPDATE total_credits SET total_credit = total_credit - ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateCreditQuery);
    $stmt->bind_param("di", $amount, $userId);

    if ($stmt->execute()) {
        // Insert the donation into the donations table
        $insertDonationQuery = "INSERT INTO donations (user_id, event, amount, date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertDonationQuery);
        $stmt->bind_param("iids", $userId, $eventId, $amount, $date);

        if ($stmt->execute()) {
            // Respond with success if both updates are successful
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to insert donation record."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update total credit."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid input data."]);
}
?>
