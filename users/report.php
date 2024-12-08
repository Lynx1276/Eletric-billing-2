<?php
// Start session and verify user authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Include database configuration
include '../database.php';

$user_id = $_SESSION['user_id'];

// Fetch connections associated with the user
$connections_query = "SELECT connection_id, connection_type FROM connections WHERE user_id = ?";
$stmt = $conn->prepare($connections_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$connections_result = $stmt->get_result();
$connections = $connections_result->fetch_all(MYSQLI_ASSOC);

// Handle the selected connection and fetch related data
$selected_connection = $_GET['connection_id'] ?? $connections[0]['connection_id'] ?? null;

if ($selected_connection) {
    // Fetch billing details
    $billing_query = "SELECT billing_month, units_consumed, total_cost, bill_status FROM billing WHERE connection_id = ? ORDER BY billing_month DESC";
    $stmt = $conn->prepare($billing_query);
    $stmt->bind_param("i", $selected_connection);
    $stmt->execute();
    $billing_result = $stmt->get_result();
    $bills = $billing_result->fetch_all(MYSQLI_ASSOC);

    // Fetch meter readings
    $meters_query = "SELECT reading_date, current_reading FROM meters WHERE connection_id = ? ORDER BY reading_date DESC";
    $stmt = $conn->prepare($meters_query);
    $stmt->bind_param("i", $selected_connection);
    $stmt->execute();
    $meters_result = $stmt->get_result();
    $meters = $meters_result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electric Billing Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Main Content -->
    <div class="ml-80 p-10">
        <h1 class="text-2xl font-bold text-blue-500 mb-4">Reports</h1>

        <!-- Export Button -->
        <form method="POST" action="export_report.php" class="mt-6">
            <input type="hidden" name="connection_id" value="<?= $selected_connection; ?>">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Export to PDF</button>
        </form>

        <!-- Billing and Meter Chart Container (Flexbox Layout) -->
        <div class="flex justify-between gap-6 mt-6">
            <!-- Billing Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md w-full md:w-1/2">
                <h2 class="text-xl font-bold text-green-500 mb-3">Billing Overview</h2>
                <canvas id="billingChart"></canvas>
            </div>

            <!-- Meter Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md w-full md:w-1/2">
                <h2 class="text-xl font-bold text-blue-500 mb-3">Meter Readings</h2>
                <canvas id="meterChart"></canvas>
            </div>
        </div>

        <!-- Display Bills Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mt-6">
            <h2 class="text-xl font-bold text-gray-700 mb-3">Billing Details</h2>
            <?php if (!empty($bills)): ?>
                <table class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b font-semibold text-left">Billing Month</th>
                            <th class="py-2 px-4 border-b font-semibold text-left">Units Consumed</th>
                            <th class="py-2 px-4 border-b font-semibold text-left">Total Cost (₱)</th>
                            <th class="py-2 px-4 border-b font-semibold text-left">Bill Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($bill['billing_month']); ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($bill['units_consumed']); ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($bill['total_cost']); ?></td>
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($bill['bill_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-500">No billing records available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        const billingData = {
            labels: <?= json_encode(array_column($bills ?? [], 'billing_month')); ?>,
            datasets: [{
                label: 'Amount Due (₱)',
                data: <?= json_encode(array_column($bills ?? [], 'total_cost')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        const meterData = {
            labels: <?= json_encode(array_column($meters ?? [], 'reading_date')); ?>,
            datasets: [{
                label: 'Meter Reading',
                data: <?= json_encode(array_column($meters ?? [], 'current_reading')); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        };

        new Chart(document.getElementById('billingChart').getContext('2d'), {
            type: 'bar',
            data: billingData,
            options: {
                responsive: true
            }
        });

        new Chart(document.getElementById('meterChart').getContext('2d'), {
            type: 'line',
            data: meterData,
            options: {
                responsive: true
            }
        });
    </script>
</body>

</html>