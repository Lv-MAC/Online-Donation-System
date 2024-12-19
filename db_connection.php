<?php
// Database connection settings
$host = 'localhost'; // Database host
$username = 'root';  // Database username (change as needed)
$password = '';      // Database password (change as needed)
$dbname = 'donate'; // Database name (replace with your database name)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to UTF-8 for handling special characters
$conn->set_charset('utf8');

// Optional: Use this line to check successful connection
// echo "Connected successfully";

// Return the connection
return $conn;
?>
