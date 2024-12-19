<?php
session_start();
include('db_connection.php'); // Include your database connection file

// Check if the user is logged in (optional)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventName = $_POST['event_name'];
    $eventDescription = $_POST['event_description'];
    $eventDate = $_POST['event_date'];

    // Handle Image Upload
    $targetDir = "assets/"; // Ensure this directory exists and is writable
    $eventImage = basename($_FILES['event_image']['name']);
    $targetFilePath = $targetDir . $eventImage;

    // Check if the file is an image
    $check = getimagesize($_FILES['event_image']['tmp_name']);
    if ($check === false) {
        echo "File is not an image.";
        exit();
    }

    // Validate file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes)) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        exit();
    }

    // Validate file size (e.g., limit to 2MB)
    if ($_FILES['event_image']['size'] > 2 * 1024 * 1024) {
        echo "Sorry, your file is too large. Maximum file size is 2MB.";
        exit();
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetFilePath)) {
        // Insert into the donation_events table
        $s_query = "INSERT INTO donation_events (event_name, event_description, event_date, event_image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($s_query);
        $stmt->bind_param("ssss", $eventName, $eventDescription, $eventDate, $targetFilePath);
        
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php"); // Redirect to admin dashboard after successful insertion
            exit();
        } else {
            echo "Error inserting record: " . $stmt->error;
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="w-full max-w-lg mx-auto mt-16">
        <h2 class="text-3xl font-bold text-center mb-6">Create Event</h2>
        <form action="create_event.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="event_name" class="block text-gray-700">Event Name</label>
                <input type="text" id="event_name" name="event_name" class="w-full px-4 py-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="event_description" class="block text-gray-700">Event Description</label>
                <textarea id="event_description" name="event_description" class="w-full px-4 py-2 border border-gray-300 rounded" required></textarea>
            </div>
            <div class="mb-4">
                <label for="event_image" class="block text-gray-700">Event Image</label>
                <input type="file" id="event_image" name="event_image" class="w-full p-2 mt-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="event_date" class="block text-gray-700">Event Date</label>
                <input type="date" id="event_date" name="event_date" class="w-full p-2 mt-2 border border-gray-300 rounded" required>
            </div>
            <div class="flex justify-end">
                <button type=" submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Create Event</button>
            </div>
        </form>
    </div>
</body>
</html>