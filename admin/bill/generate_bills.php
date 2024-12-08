<?php
session_start();
include '../../database.php';

// Fetch all users and their connection IDs
$usersQuery = "
    SELECT users.user_id, connections.connection_id 
    FROM users 
    JOIN connections ON users.user_id = connections.user_id";

$usersResult = $conn->query($usersQuery);

if ($usersResult->num_rows > 0) {
    $billing_month = date('Y-m-01'); // First day of the current month
    $cost_per_unit = 0.12; // Example rate per unit
    $units_consumed = 500; // Example consumption per user

    while ($user = $usersResult->fetch_assoc()) {
        $user_id = $user['user_id'];
        $connection_id = $user['connection_id']; // Get connection_id
        $admin_id = 1; // Replace with the actual admin_id or fetch dynamically
        $total_cost = $units_consumed * $cost_per_unit;

        // Insert a bill for the user
        $insertBillQuery = "
            INSERT INTO billing 
            (user_id, connection_id, admin_id, billing_month, units_consumed, cost_per_unit, due_date, bill_status) 
            VALUES 
            ('$user_id', '$connection_id', '$admin_id', '$billing_month', '$units_consumed', '$cost_per_unit', DATE_ADD(NOW(), INTERVAL 15 DAY), 'Unpaid')";

        // Execute the query
        if (!$conn->query($insertBillQuery)) {
            $_SESSION['error'] = 'Error inserting bill for user ID: ' . $user_id . ' - ' . $conn->error;
            header('Location: ../bills.php');
            exit();
        }
    }

    $_SESSION['message'] = 'Bills generated for all users successfully!';
    header('Location: ../bills.php');
    exit();
} else {
    $_SESSION['error'] = 'No users with connections found to generate bills.';
    header('Location: ../bills.php');
    exit();
}
?>
