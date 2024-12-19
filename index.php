<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if user is not logged in
    exit();
}

$userId = $_SESSION['user_id'];

// Connect to the database
$mysqli = new mysqli('localhost', 'root', '', 'donate');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch the user's username from the users table
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['username'];

// Fetch the user's total credit from the total_credits table
$sql = "SELECT total_credit FROM total_credits WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalCredit = 0;
if ($credit = $result->fetch_assoc()) {
    $totalCredit = $credit['total_credit'];
}

// Fetch all donation events
$eventSql = "SELECT id, event_name, event_description, event_image FROM donation_events";
$eventStmt = $mysqli->prepare($eventSql);
$eventStmt->execute();
$eventResult = $eventStmt->get_result();

$stmt->close();
$eventStmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donate Bangladesh</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

  <!-- Tailwind & DaisyUI -->
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Custom CSS -->
  <style>
    .manrope {
      font-family: "Manrope", sans-serif;
    }
    body {
      overflow-x: hidden;
    }
  </style>
</head>
<body>

  <!-- Header Section -->
  <header class="manrope fixed w-full z-20 top-0 start-0">
    <section class="w-full">
      <div class="navbar bg-orange-50 p-10">
        <!-- Navbar Start -->
        <div class="navbar-start">
          <a href="blog.html" class="mx-3 text-xl font-extrabold btn bg-lime-500">Blog</a>
          <a href="dashboard.html" class="mx-3 text-xl font-extrabold">Hello, <span id="username"><?php echo htmlspecialchars($username); ?></span></a>
        </div>
        <!-- Navbar Center -->
        <div class="navbar-center hidden lg:flex">
          <img src="assets/logo.png" alt="Logo">
          <h1 class="font-extrabold ml-5">Donate Bangladesh</h1>
        </div>
        <!-- Navbar End -->
        <div class="navbar-end">
          <img src="assets/coin.png" alt="Coin" />
          <h1 class="pl-2">Current Credit: <span id="total-credits"><?php echo number_format($totalCredit, 2); ?></span> BDT</h1>
          <a href="dashboard.php" class="bg-blue-500 text-white mx-3 py-2 px-4 rounded-md">Go to Dashboard</a>
          <a href="logout_user.php" class="btn bg-red-500 text-white ml-4">Logout</a>
        </div>
      </div>
    </section>
  </header>

  <!-- Main Content Section -->
  <section class="w-full mt-52">
    <div id="expense-form" class="container mx-auto">

      <!-- Donation Cards -->
      <?php while ($event = $eventResult->fetch_assoc()): ?>
      <div class="hero text-letter-sm rounded-lg border border-slate-5000 pt-10 pb-10 mt-5 mb-5 donation-card">
        <div class="hero-content flex-col lg:flex-row">
          <img src="<?php echo htmlspecialchars($event['event_image']); ?>" class="max-w-sm rounded-lg shadow-2xl" />
          <div>
            <h1 class="text-xl font-bold event_name"><?php echo htmlspecialchars($event['event_name']); ?></h1>
            <p class="py-6 text-xs">
              <?php echo htmlspecialchars($event['event_description']); ?>
            </p>
            <!-- Donation Amount Input -->
            <div class="form-control">
              <input type="number" placeholder="Write Donation Amount" class="input input-bordered amount-input" required />
            </div>
            <!-- Donate Now Button -->
            <div class="form-control mt-4">
              <button class="btn bg-blue-500 text-white donate-now-btn" data-event-id="<?php echo $event['id']; ?>">Donate Now</button>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>

    </div>
  </section>

  <script>
    // Handle the donation button click
    document.querySelectorAll('.donate-now-btn').forEach(button => {
      button.addEventListener('click', function () {
        const amount = this.parentElement.previousElementSibling.querySelector('input').value;

        if (amount <= 0) {
          alert("Please enter a valid donation amount.");
          return;
        }

        const userId = <?php echo $userId; ?>;
        const eventId = this.getAttribute('data-event-id');

        // Send the donation request to the server using fetch API
        fetch('update_credit.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            user_id: userId,
            event_id: eventId,
            amount: amount,
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert("Donation successful!");
            const totalCreditsEl = document.getElementById('total-credits');
            const newCredit = parseFloat(totalCreditsEl.innerText.replace(/,/g, '')) - parseFloat(amount);
            totalCreditsEl.innerText = newCredit.toFixed(2); // Update total credit dynamically
          } else {
            alert("Donation failed. Please try again.");
          }
        })
        .catch(error => {
          alert("An error occurred. Please try again.");
          console.error(error);
        });
      });
    });
  </script>

</body>
</html>
