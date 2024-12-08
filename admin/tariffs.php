<?php
session_start();
include '../database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login.php");
  exit;
}

// Fetch tariffs
$tariffsQuery = "SELECT * FROM tariffs";
$tariffsResult = $conn->query($tariffsQuery);

// Handle tariff edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tariff'])) {
  $tariff_id = $_POST['tariff_id'];
  $connection_type = $_POST['connection_type'];
  $cost_per_unit = $_POST['cost_per_unit'];
  $effective_date = $_POST['effective_date'];

  $updateQuery = "UPDATE tariffs SET 
                    connection_type = ?, 
                    cost_per_unit = ?, 
                    effective_date = ?
                    WHERE tariff_id = ?";
  $stmt = $conn->prepare($updateQuery);
  $stmt->bind_param("sdsi", $connection_type, $cost_per_unit, $effective_date, $tariff_id);
  $stmt->execute();
  $stmt->close();

  // Refresh to show updated data
  header("Location: tariffs.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Online Electric Billings</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
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
        <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600  hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
        <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
        <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
        <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
        <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 pl-80">
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Manage Tariffs</h1>
      <table class="min-w-full bg-white shadow rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Tariff ID</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Admin ID</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Connection Type</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Cost Per Unit</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Effective Date</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($tariff = $tariffsResult->fetch_assoc()): ?>
            <tr>
              <form method="POST" action="">
                <td class="px-4 py-2"><input type="hidden" name="tariff_id" value="<?= $tariff['tariff_id']; ?>"><?= $tariff['tariff_id']; ?></td>
                <td class="px-4 py-2"><?= $tariff['admin_id']; ?></td>
                <td class="px-4 py-2"><input type="text" name="connection_type" value="<?= htmlspecialchars($tariff['connection_type']); ?>" class="border rounded p-1 w-full"></td>
                <td class="px-4 py-2"><input type="number" step="0.01" name="cost_per_unit" value="<?= number_format($tariff['cost_per_unit'], 2); ?>" class="border rounded p-1 w-full"></td>
                <td class="px-4 py-2"><input type="date" name="effective_date" value="<?= $tariff['effective_date']; ?>" class="border rounded p-1 w-full"></td>
                <td class="px-4 py-2">
                  <button type="submit" name="edit_tariff" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </td>
              </form>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>