<?php
session_start();
include('db_connection.php');

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $eventId = $_GET['id'];

    // Fetch the event details from the database
    $query = "SELECT * FROM donation_events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Update event details
        $eventName = $_POST['event_name'];
        $eventDescription = $_POST['event_description'];
        $eventDate = $_POST['event_date'];

        // Update query
        $updateQuery = "UPDATE donation_events SET event_name = ?, event_description = ?, event_date = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sssi", $eventName, $eventDescription, $eventDate, $eventId);
        $updateStmt->execute();

        header("Location: admin_dashboard.php");
        exit();
    }
} else {
    echo "No ID specified.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="w-full max-w-lg mx-auto mt-16">
        <h2 class="text-3xl font-bold text-center mb-6">Edit Event</h2>
        <form action="" method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="event_name" class="block text-gray-700">Event Name</label>
                <input type="text" id="event_name" name="event_name" class="w-full px-4 py-2 border rounded-md" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
            </div>
            <div class="mb-4">
                <label for="event_description" class="block text-gray-700">Event Description</label>
                <textarea id="event_description" name="event_description" class="w-full px-4 py-2 border rounded-md" required><?php echo htmlspecialchars($event['event_description']); ?></textarea>
            </div>
            <div class="mb-4">
                <label for="event_date" class="block text-gray-700">Event Date</label>
                <input type="date" id="event_date" name="event_date" class="w-full px-4 py-2 border rounded-md" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md">Update Event</button>
        </form>
    </div>
</body>
</html>