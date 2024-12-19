<?php
session_start();
include('db_connection.php');  // Include database connection

// Ensure the user is logged in by checking the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id'];  // Get the user ID from the session

// Fetch user information from the database
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

$username = $user['username'];
$email = $user['email'];
$phone = $user['phone'];
$address = $user['address'];
$nid = $user['nid'];

// Fetch user's donation history
$donationQuery = "SELECT * FROM donations WHERE user_id = ?";
$stmt = $conn->prepare($donationQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$donationResult = $stmt->get_result();

// Fetch user's credit history from the `credits` table
$creditQuery = "SELECT * FROM credits WHERE user_id = ?";
$stmt = $conn->prepare($creditQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$creditResult = $stmt->get_result();

// Fetch total credit from the `total_credits` table
$totalCreditQuery = "SELECT total_credit FROM total_credits WHERE user_id = ?";
$stmt = $conn->prepare($totalCreditQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalCreditResult = $stmt->get_result();
$totalCreditRow = $totalCreditResult->fetch_assoc();
$totalCredit = $totalCreditRow['total_credit'] ?: 0;  // If no credit, default to 0

// Update user information if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newEmail = $_POST['email'];
    $newPhone = $_POST['phone'];
    $newAddress = $_POST['address'];
    $newNid = $_POST['nid'];

    // Update user info in the database
    $updateQuery = "UPDATE users SET email = ?, phone = ?, address = ?, nid = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssi", $newEmail, $newPhone, $newAddress, $newNid, $userId);
    if ($stmt->execute()) {
        // Refresh the page to reflect the updated info
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating user information.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto my-10">
        <!-- Dashboard Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-semibold">Welcome to Your Dashboard, <?php echo htmlspecialchars($username); ?>!</h1>
        </div>

        <!-- Dashboard Cards -->
        <div class=" mb-5">
            <!-- Personal Information Card -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <!-- Buttons Section -->
                <div class="text-center space-x-4">
                    <!-- Go to Home Button -->
                    <a href="index.php" class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600">Go to Home</a>

                    <!-- Logout Button -->
                    <a href="login.html" class="bg-red-500 text-white px-6 py-3 rounded-md hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Personal Information Card -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-800">Personal Information</h2>
                <form id="user-info-form" method="POST">
                    <div class="mt-4">
                        <div class="flex items-center mb-4">
                            <span class="w-1/3 font-bold text-gray-700">Username:</span>
                            <span id="usernameMain" class="text-gray-600"><?php echo htmlspecialchars($username); ?></span>
                        </div>
                        <div class="flex items-center mb-4">
                            <span class="w-1/3 font-bold text-gray-700">Email:</span>
                            <input type="email" id="user-email" name="email" class="w-2/3 p-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($email); ?>" readonly>
                        </div>
                        <div class="flex items-center mb-4">
                            <span class="w-1/3 font-bold text-gray-700">Phone:</span>
                            <input type="text" id="user-phone" name="phone" class="w-2/3 p-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($phone); ?>" readonly>
                        </div>
                        <div class="flex items-center mb-4">
                            <span class="w-1/3 font-bold text-gray-700">Address:</span>
                            <input type="text" id="user-address" name="address" class="w-2/3 p-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($address); ?>" readonly>
                        </div>
                        <div class="flex items-center mb-4">
                            <span class="w-1/3 font-bold text-gray-700">NID:</span>
                            <input type="text" id="user-nid" name="nid" class="w-2/3 p-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($nid); ?>" readonly>
                        </div>
                        <button type="button" id="edit-btn" class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4">Edit</button>
                        <button type="submit" id="save-btn" class="bg-green-500 text-white px-4 py-2 rounded-md mt-4 hidden">Save</button>
                    </div>
                </form>
            </div>

            <!-- Donation History Card -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Donation History</h2>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $donationResult->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['date']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['amount']); ?> BDT</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Credit History Section (Another Card) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
            <!-- Add Credit Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-800">Add Credit</h2>
                <form id="add-credit-form" action="add_credit.php" method="POST">
                    <div class="flex flex-col">
                        <label for="amount" class="mb-2 text-gray-700">Enter Credit Amount (BDT):</label>
                        <input type="number" name="amount" id="amount" class="p-2 border border-gray-300 rounded-md mb-4" required>
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">  <!-- Hidden input for user ID -->
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Add Credit</button>
                    </div>
                </form>
            </div>

            <!-- Credit History Card -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Credit History</h2>
                <p>Total Credit: <?php echo $totalCredit; ?> BDT</p>
                <table class="w-full mt-4">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($creditRow = $creditResult->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($creditRow['date']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($creditRow['amount']); ?> BDT</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to handle the "Edit" and "Save" buttons for user information
        document.getElementById('edit-btn').addEventListener('click', function() {
            document.getElementById('user-email').removeAttribute('readonly');
            document.getElementById('user-phone').removeAttribute('readonly');
            document.getElementById('user-address').removeAttribute('readonly');
            document.getElementById('user-nid').removeAttribute('readonly');
            document.getElementById('edit-btn').classList.add('hidden');
            document.getElementById('save-btn').classList.remove('hidden');
        });
    </script>
</body>
</html>
