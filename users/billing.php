<?php
session_start();
include '../database.php';

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    die("User ID is not set in session. Please log in again.");
}
$user_id = $_SESSION['user_id'];


// Test database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch billing records
$sql = "SELECT * FROM billing WHERE connection_id IN (
            SELECT connection_id FROM connections WHERE user_id = ?
        )";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    
    $bills = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $bills = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="fixed h-full bg-white text-black w-72 shadow-lg border-r-2 border-gray-200">
    <div class="p-6">
        <h1 class="text-3xl font-extrabold text-blue-600">Online Electric Billing</h1>
        <p class="mt-2 text-lg text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['name']); ?></p>
        <div class="mt-6 flex items-center space-x-4">
            <!-- User Profile -->
            <span class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                <i class="fa-solid fa-user text-2xl text-gray-400"></i>
            </span>
            <div>
                <p class="font-semibold text-lg"><?= htmlspecialchars($_SESSION['name']); ?></p>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($_SESSION['email']); ?></p>
            </div>
        </div>
    </div>
    <hr>
    <nav class="mt-10">
        <ul class="space-y-4">
            <li class="block px-4 py-3 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-200">
                <a href="index.php" class="flex items-center space-x-3">
                    <i class="fa-solid fa-house-user"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="block px-4 py-3 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-200">
                <a href="billing.php" class="flex items-center space-x-3">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Bills</span>
                </a>
            </li>
            <li class="block px-4 py-3 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-200">
                <a href="payments.php" class="flex items-center space-x-3">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="block px-4 py-3 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-200">
                <a href="report.php" class="flex items-center space-x-3">
                <i class="fa-solid fa-paperclip"></i>
                    <span>Report</span>
                </a>
            </li>
            <li class="block px-4 py-3 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-200">
                <a href="complaints.php" class="flex items-center space-x-3">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <span>Complaints</span>
                </a>
            </li>
            <li class="block px-4 py-3 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-200">
                <a href="feedback.php" class="flex items-center space-x-3">
                    <i class="fa-solid fa-comment-alt"></i>
                    <span>Feedback</span>
                </a>
            </li>
            <li class="block px-4 py-3 rounded-lg hover:bg-red-500 hover:text-white transition-all duration-200">
                <a href="../logout.php" class="flex items-center space-x-3">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>


    <div class="ml-72 p-10">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-blue-500 mb-5">Your Bills</h1>
            <?php if (!empty($bills)): ?>
                <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-blue-500 text-white">
                        <tr>
                            <th class="p-3">Billing Month</th>
                            <th class="p-3">Units Consumed</th>
                            <th class="p-3">Total Cost</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <tr class="border-b">
                                <td class="p-3 text-center"><?= htmlspecialchars($bill['billing_month']) ?></td>
                                <td class="p-3 text-center"><?= htmlspecialchars($bill['units_consumed']) ?></td>
                                <td class="p-3 text-center">₱<?= htmlspecialchars($bill['total_cost']) ?></td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-1 rounded <?= $bill['bill_status'] == 'Paid' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' ?>">
                                        <?= htmlspecialchars($bill['bill_status']) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-center"><?= htmlspecialchars($bill['due_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-gray-500">No billing records found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
