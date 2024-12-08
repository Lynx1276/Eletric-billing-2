<?php
session_start();
include '../database.php';

// Check if the admin is logged in
$admin = isset($_SESSION['admin_id']);

// Fetching data for the charts from the database
// Chart 1: Number of connections by type
$queryConnections = "SELECT connection_type, COUNT(*) as count FROM connections GROUP BY connection_type";
$resultConnections = mysqli_query($conn, $queryConnections);
$connectionsData = [];
while ($row = mysqli_fetch_assoc($resultConnections)) {
  $connectionsData[] = $row;
}

// Chart 2: Number of outages by type (Scheduled vs Unscheduled)
$queryOutages = "SELECT outage_type, COUNT(*) as count FROM outages GROUP BY outage_type";
$resultOutages = mysqli_query($conn, $queryOutages);
$outagesData = [];
while ($row = mysqli_fetch_assoc($resultOutages)) {
  $outagesData[] = $row;
}

// Chart 3: Meter readings over time (monthly)
$queryReadings = "SELECT DATE_FORMAT(reading_date, '%Y-%m') as month, SUM(units_consumed) as total_units FROM meters GROUP BY month ORDER BY month";
$resultReadings = mysqli_query($conn, $queryReadings);
$readingsData = [];
while ($row = mysqli_fetch_assoc($resultReadings)) {
  $readingsData[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Online Electric Billings</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
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
        <li><a href="admin.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-700 transition duration-200">Dashboard</a></li>
        <li><a href="users.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800  hover:bg-gray-200 transition duration-200">Manage Users</a></li>
        <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Meters</a></li>
        <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Bills</a></li>
        <li><a href="connections.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Connections</a></li>
        <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
        <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
        <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
        <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200 transition duration-200">Reports</a></li>
        <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
      </ul>
    </div>


    <!-- Main Content -->
    <div class="flex-1 p-8 pl-80"> <!-- Added padding-left to offset the sidebar -->
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Reports</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Number of connections by type -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Connections by Type</h2>
          <canvas id="connectionsChart"></canvas>
        </div>

        <!-- Chart 2: Number of outages by type -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Outages by Type</h2>
          <canvas id="outagesChart"></canvas>
        </div>

        <!-- Chart 3: Meter readings over time -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Meter Readings over Time</h2>
          <canvas id="readingsChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Chart 1: Connections by Type
    const ctx1 = document.getElementById('connectionsChart').getContext('2d');
    const connectionsChart = new Chart(ctx1, {
      type: 'pie',
      data: {
        labels: <?php echo json_encode(array_column($connectionsData, 'connection_type')); ?>,
        datasets: [{
          label: 'Connections',
          data: <?php echo json_encode(array_column($connectionsData, 'count')); ?>,
          backgroundColor: ['#4CAF50', '#FF5733'],
        }]
      }
    });

    // Chart 2: Outages by Type
    const ctx2 = document.getElementById('outagesChart').getContext('2d');
    const outagesChart = new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_column($outagesData, 'outage_type')); ?>,
        datasets: [{
          label: 'Outages',
          data: <?php echo json_encode(array_column($outagesData, 'count')); ?>,
          backgroundColor: ['#3498db', '#f39c12'],
        }]
      }
    });

    // Chart 3: Meter Readings over Time
    const ctx3 = document.getElementById('readingsChart').getContext('2d');
    const readingsChart = new Chart(ctx3, {
      type: 'line',
      data: {
        labels: <?php echo json_encode(array_column($readingsData, 'units_conumed')); ?>,
        datasets: [{
          label: 'Units Consumed',
          data: <?php echo json_encode(array_column($readingsData, 'total_units')); ?>,
          borderColor: '#2ecc71',
          fill: false,
        }]
      }
    });
  </script>
</body>

</html>