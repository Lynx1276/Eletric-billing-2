<?php
include '../database.php';

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'users':
        $query = "SELECT user_id, name, email FROM users";
        $result = $conn->query($query);
        echo "<h3 class='text-xl font-semibold'>User List</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['name']} ({$row['email']})</li>";
        }
        echo "</ul>";
        break;

    case 'admins':
        $query = "SELECT admin_id, name, email FROM admins";
        $result = $conn->query($query);
        echo "<h3 class='text-xl font-semibold'>Admin List</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['name']} ({$row['email']})</li>";
        }
        echo "</ul>";
        break;

    case 'complaints':
        $query = "SELECT complaint_id, complaint_type, status FROM complaints";
        $result = $conn->query($query);
        echo "<h3 class='text-xl font-semibold'>Complaints</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['complaint_type']} - Status: {$row['status']}</li>";
        }
        echo "</ul>";
        break;

    case 'feedbacks':
        $query = "SELECT feedback_id, feedback_text FROM feedback";
        $result = $conn->query($query);
        echo "<h3 class='text-xl font-semibold'>Feedback</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['feedback_text']}</li>";
        }
        echo "</ul>";
        break;

    case 'bills_total':
        $query = "SELECT SUM(total_cost) AS total_bills FROM billing";
        $result = $conn->query($query);
        $data = $result->fetch_assoc();
        echo "<h3 class='text-xl font-semibold'>Total Billing Amount</h3>";
        echo "<p class='text-2xl font-bold'>$" . number_format($data['total_bills'], 2) . "</p>";
        break;

    case 'bills_unpaid':
        $query = "SELECT billing.total_cost, users.name AS name 
                          FROM billing 
                          JOIN users ON billing.user_id = users.user_id 
                          WHERE billing.bill_status = 'unpaid'";
        $result = $conn->query($query);
        echo "<h3 class='text-xl font-semibold'>Unpaid Bills</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>User: {$row['name']}, Amount: $" . number_format($row['total_cost'], 2) . "</li>";
        }
        echo "</ul>";
        break;

    case 'bills_paid':
        $query = "SELECT billing.total_cost, users.name AS name 
                              FROM billing 
                              JOIN users ON billing.user_id = users.user_id 
                              WHERE billing.bill_status = 'paid'";
        $result = $conn->query($query);
        echo "<h3 class='text-xl font-semibold'>Paid Bills</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>User: {$row['name']}, Amount: $" . number_format($row['total_cost'], 2) . "</li>";
        }
        echo "</ul>";
        break;
                

    default:
        echo "<p>No data available.</p>";
        break;
}
?>
