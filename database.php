<?php


$host = 'localhost';           // Your database host
$username = 'root';            // Your database username
$password = '';                // Your database password
$db_name = 'billings'; // Your database name

// Create a connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
