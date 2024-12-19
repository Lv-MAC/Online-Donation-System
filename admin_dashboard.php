<?php
session_start();
include('db_connection.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$creditsQuery = "SELECT c.credit_id, c.amount, c.date, u.username FROM credits c JOIN users u ON c.user_id = u.user_id";
$creditsResult = $conn->query($creditsQuery);

// Fetch total donations for each event
$totalDonationsQuery = "SELECT d.event_name, SUM(amount) AS total_amount FROM donations JOIN donation_events d ON d.id = donations.event GROUP BY donations.event";
$totalDonationsResult = $conn->query($totalDonationsQuery);

// Fetch all donation events
$eventsQuery = "SELECT * FROM donation_events";
$eventsResult = $conn->query($eventsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto my-6 p-6">
    <h1 class="text-3xl font-semibold text-gray-700 mb-6">Admin Dashboard</h1>

    <!-- Logout Button -->
    <div class="mb-4">
        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
    </div>

    <!-- View All Transactions (Credits) -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">All Transactions (Credits)</h2>
        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">User Name</th>
                    <th class="px-4 py-2 text-left">Amount</th>
                    <th class="px-4 py-2 text-left">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($creditRow = $creditsResult->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($creditRow['username']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($creditRow['amount']); ?> BDT</td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($creditRow['date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- View Donations by Event -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Total Donations per Event</h2>
        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Event</th>
                    <th class="px-4 py-2 text-left">Total Donation (BDT)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($totalDonationRow = $totalDonationsResult->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($totalDonationRow['event_name']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($totalDonationRow['total_amount']); ?> BDT</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Donation Event -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Create Donation Event</h2>
        <form action="create_event.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="event_name" class="block text-gray-700">Event Name</label>
                <input type="text" id="event_name" name="event_name" class="w-full p-2 mt-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="event_description" class="block text-gray-700">Event Description</label>
                <textarea id="event_description" name="event_description" class="w-full p-2 mt-2 border border-gray-300 rounded" required></textarea>
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
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Create Event</button>
            </div>
        </form>
    </div>

    <!-- View All Donation Events -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">All Donation Events</h2>
        <table class="w-full mt-4">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Event Name</th>
                    <th class="px-4 py-2 text-left">Description</th>
                    <th class="px-4 py-2 text-left">Event Date</th>
                    <th class="px-4 py-2 text-left">Image</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($eventRow = $eventsResult->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($eventRow['event_name']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($eventRow['event_description']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($eventRow['event_date']); ?></td>
                        <td class="px-4 py-2">
                            <?php if (!empty($eventRow['event_image'])): ?>
                                <img src="<?php echo htmlspecialchars($eventRow['event_image']); ?>" alt="Event Image" class="w-16 h-16 object-cover rounded">
                            <?php else: ?>
                                No image available
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2">
                        <a href="edit_event.php?id=<?php echo urlencode($eventRow['id']); ?>" class="text-blue-500 hover:text-blue-700">Edit</a>
                        <a href="delete_event.php?id=<?php echo urlencode($eventRow['id']); ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
