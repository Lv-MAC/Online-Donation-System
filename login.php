<?php
// Start the session
session_start();

// Include the database connection
include('db_connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get username and password from form
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Query to find the user by username
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username); // Bind username
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the username exists
    if ($result->num_rows == 0) {
        $_SESSION['login_error'] = "Invalid username or password!";
        header("Location: login.php");
        exit;
    }

    // Fetch user data
    $user = $result->fetch_assoc();
    $hashedPassword = $user['password'];

    // Verify the password
    if (password_verify($password, $hashedPassword)) {
        // Store session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];  // Store username in session

        // Redirect to dashboard.php
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['login_error'] = "Invalid username or password!";
        header("Location: login.php");
        exit;
    }
}
?>
