<?php
// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page (or home page)
header("Location: index.php"); // Adjust the redirect URL as needed
exit();
?>
