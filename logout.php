<?php
session_start(); // Start the session
session_destroy(); // Destroy all session data
header("Location: admin_login.php"); // Redirect to admin login page
exit();
?>