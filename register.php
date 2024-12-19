<?php
// Connect to the database
require 'db_connection.php'; // Including the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "All fields are required!";
        exit;
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    // Check if username already exists
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username already exists. Please choose a different one.";
        exit;
    }

    // Hash the password before saving it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database, leaving phone, address, and nid as NULL
    $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Redirect to login page after successful registration
        header("Location: login.html"); // Redirect to login page
        exit; // Make sure to exit after redirection
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
