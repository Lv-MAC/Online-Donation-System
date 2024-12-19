<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventName = $_POST['event_name'];
    $eventDescription = $_POST['event_description'];
    
    // Handle Image Upload
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["event_image"]["name"]);
    if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $targetFile)) {
        // Insert into the donation_events table
        $query = "INSERT INTO donation_events (event_name, event_description, event_image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $eventName, $eventDescription, $targetFile);
        $stmt->execute();
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
