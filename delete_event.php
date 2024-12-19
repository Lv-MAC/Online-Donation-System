<?php
session_start();
include('db_connection.php');

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $eventId = $_GET['id'];

    // Prepare a statement to delete the event
    $query = "DELETE FROM donation_events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventId);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php"); // Redirect after deletion
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "No ID specified.";
}
?>