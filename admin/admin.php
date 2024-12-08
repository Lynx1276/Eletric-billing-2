<?php
session_start();
include '../database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Stats Queries
$statsQueries = [
    "users" => "SELECT COUNT(*) AS count FROM users",
    "admins" => "SELECT COUNT(*) AS count FROM admins",
    "complaints" => "SELECT COUNT(*) AS count FROM complaints",
    "feedbacks" => "SELECT COUNT(*) AS count FROM feedback",
    "bills_total" => "SELECT SUM(total_cost) AS total FROM billing",
    "bills_unpaid" => "SELECT COUNT(*) AS count FROM billing WHERE bill_status = 'unpaid'",
    "bills_paid" => "SELECT COUNT(*) AS count FROM billing WHERE bill_status = 'paid'"
];


$stats = [];
foreach ($statsQueries as $key => $query) {
    $result = $conn->query($query);
    $stats[$key] = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-white text-black p-6 shadow-lg w-72 fixed h-full">
            <h2 class="text-2xl font-semibold text-gray-800 mb-8">Online Electric Billing | Admin Dashboard</h2>

            <!-- User Profile Section -->
            <div class="p-4 bg-gray-50 rounded-lg shadow-sm mb-8">
                <p class="text-lg text-gray-600 mb-2">Welcome, <?= htmlspecialchars($_SESSION['name']); ?></p>
                <div class="flex items-center space-x-4">
                    <!-- User Profile Icon -->
                    <span class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-user text-2xl text-gray-400"></i>
                    </span>
                    <div>
                        <p class="font-semibold text-lg"><?= htmlspecialchars($_SESSION['name']); ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($_SESSION['email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul>
                <li><a href="admin.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-700 transition duration-200">Dashboard</a></li>
                <li><a href="users.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800  hover:bg-gray-200 transition duration-200">Manage Users</a></li>
                <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Meters</a></li>
                <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Bills</a></li>
                <li><a href="connections.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Connections</a></li>
                <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
                <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
                <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
                <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
                <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8 pl-80">
            <h1 class="text-4xl font-semibold mb-6">Welcome, <?= htmlspecialchars($_SESSION['name']); ?></h1>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div data-type="users" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Total Users</h3>
                    <p class="text-2xl font-bold"><?= $stats['users']['count'] ?></p>
                </div>
                <div data-type="admins" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Total Admins</h3>
                    <p class="text-2xl font-bold"><?= $stats['admins']['count'] ?></p>
                </div>
                <div data-type="complaints" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Complaints</h3>
                    <p class="text-2xl font-bold"><?= $stats['complaints']['count'] ?></p>
                </div>
                <div data-type="feedbacks" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Feedback</h3>
                    <p class="text-2xl font-bold"><?= $stats['feedbacks']['count'] ?></p>
                </div>
                <div data-type="bills_total" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Total Bills</h3>
                    <p class="text-2xl font-bold">$<?= number_format($stats['bills_total']['total'], 2) ?></p>
                </div>
                <div data-type="bills_unpaid" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Unpaid Bills</h3>
                    <p class="text-2xl font-bold"><?= $stats['bills_unpaid']['count'] ?></p>
                </div>
                <div data-type="bills_paid" class="card bg-white p-6 rounded-lg shadow-lg cursor-pointer hover:bg-gray-100">
                    <h3 class="text-xl font-semibold">Paid Bills</h3>
                    <p class="text-2xl font-bold"><?= $stats['bills_paid']['count'] ?></p>
                </div>

            </div>

            <!-- Dynamic Content -->
            <div id="dynamic-content" class="mt-8 bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold">Click a card to view details.</h3>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart 1 -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold mb-4">Statistics Overview</h3>
                    <canvas id="adminChart" width="400" height="200"></canvas>
                </div>

                <!-- Chart 2 -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold mb-4">Billing Status</h3>
                    <canvas id="billingChart" width="400" height="200"></canvas>
                </div>
            </div>


        </div>
    </div>

    <script>
        // Chart Data
        const stats = <?= json_encode($stats); ?>;

        // Prepare Data for the Chart
        const labels = ["Users", "Admins", "Complaints", "Feedback"];
        const data = [
            stats.users.count,
            stats.admins.count,
            stats.complaints.count,
            stats.feedbacks.count
        ];

        // Initialize Chart.js
        const ctx = document.getElementById('adminChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', // Choose chart type (bar, line, pie, etc.)
            data: {
                labels: labels,
                datasets: [{
                    label: 'Count',
                    data: data,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Data for Billing Chart
        const billingData = {
            labels: ['Unpaid Bills', 'Paid Bills'], // Removed "Total Cost"
            datasets: [{
                data: [
                    stats.bills_unpaid.count, // Fixed access for unpaid bills count
                    stats.bills_paid.count // Fixed access for paid bills count
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)', // Red for unpaid
                    'rgba(75, 192, 192, 0.2)' // Green for paid
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)', // Red border
                    'rgba(75, 192, 192, 1)' // Green border
                ],
                borderWidth: 0.5
            }]
        };


        // Initialize Billing Chart
        const billingCtx = document.getElementById('billingChart').getContext('2d');
        new Chart(billingCtx, {
            type: 'pie', // Changed to 'doughnut' if you want a different look
            data: billingData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top', // Adjusted position
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                return `${context.label}: ${value}`; // Display value with label
                            }
                        }
                    }
                }
            }
        });




        $(document).ready(function() {
            $(".card").click(function() {
                const type = $(this).data("type");
                $.ajax({
                    url: "fetch_data.php",
                    type: "GET",
                    data: {
                        type: type
                    },
                    success: function(response) {
                        $("#dynamic-content").html(response);
                    },
                    error: function() {
                        $("#dynamic-content").html("<p>Error loading data.</p>");
                    }
                });
            });
        });
    </script>
</body>

</html>