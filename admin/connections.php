<?php
session_start();
include '../database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}

// Fetch all connections
$connectionsQuery = "SELECT * FROM connections";
$connectionsResult = $conn->query($connectionsQuery);

// Handle adding a new connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_connection'])) {
  $user_id = $_POST['user_id'];
  $connection_type = $_POST['connection_type'];
  $meter_number = $_POST['meter_number'];

  $insertQuery = "
        INSERT INTO connections (user_id, connection_type, connection_status, meter_number) 
        VALUES (?, ?, 'Active', ?)
    ";
  $stmt = $conn->prepare($insertQuery);
  $stmt->bind_param("iss", $user_id, $connection_type, $meter_number);

  if ($stmt->execute()) {
    echo "<script>alert('Connection added successfully!'); window.location.href = 'connections.php';</script>";
  } else {
    echo "<script>alert('Failed to add connection: " . $stmt->error . "');</script>";
  }
  $stmt->close();
}

// Handle updating connection status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $connection_id = $_POST['connection_id'];

  // Fetch the current status of the connection
  $statusQuery = "SELECT connection_status FROM connections WHERE connection_id = ?";
  $stmt = $conn->prepare($statusQuery);
  $stmt->bind_param("i", $connection_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $connection = $result->fetch_assoc();
  $currentStatus = $connection['connection_status'];

  // Toggle status
  $newStatus = ($currentStatus === 'Active') ? 'Inactive' : 'Active';

  $updateQuery = "UPDATE connections SET connection_status = ? WHERE connection_id = ?";
  $stmt = $conn->prepare($updateQuery);
  $stmt->bind_param("si", $newStatus, $connection_id);

  if ($stmt->execute()) {
    echo "<script>alert('Connection status updated successfully!'); window.location.href = 'connections.php';</script>";
  } else {
    echo "<script>alert('Failed to update connection status: " . $stmt->error . "');</script>";
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Manage Connections</title>
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
        <li><a href="users.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Users</a></li>
        <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Meters</a></li>
        <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Bills</a></li>
        <li><a href="connections.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200">Manage Connections</a></li>
        <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
        <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
        <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
        <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
        <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 pl-80">
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Manage Connections</h1>

      <!-- Add Connection Form -->
      <div class="p-6 bg-white rounded-lg shadow-lg mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add Connection</h2>
        <form method="POST">
          <div class="mb-4">
            <label for="user_id" class="block text-sm font-medium text-gray-700">User ID</label>
            <input type="number" name="user_id" id="user_id" required class="p-3 border border-gray-300 rounded-lg w-full">
          </div>
          <div class="mb-4">
            <label for="connection_type" class="block text-sm font-medium text-gray-700">Connection Type</label>
            <select name="connection_type" id="connection_type" required class="p-3 border border-gray-300 rounded-lg w-full">
              <option value="Residential">Residential</option>
              <option value="Commercial">Commercial</option>
            </select>
          </div>
          <div class="mb-4">
            <label for="meter_number" class="block text-sm font-medium text-gray-700">Meter Number</label>
            <input type="text" name="meter_number" id="meter_number" required class="p-3 border border-gray-300 rounded-lg w-full">
          </div>
          <button type="submit" name="add_connection" class="py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Connection</button>
        </form>
      </div>

      <!-- Connection Table -->
      <div class="p-6 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Connections</h2>
        <table class="min-w-full table-auto border-collapse border border-gray-300">
          <thead>
            <tr>
              <th class="border border-gray-300 px-4 py-2">Connection ID</th>
              <th class="border border-gray-300 px-4 py-2">User ID</th>
              <th class="border border-gray-300 px-4 py-2">Connection Type</th>
              <th class="border border-gray-300 px-4 py-2">Connection Status</th>
              <th class="border border-gray-300 px-4 py-2">Meter Number</th>
              <th class="border border-gray-300 px-4 py-2">Created At</th>
              <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $connectionsResult->fetch_assoc()): ?>
              <tr>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['connection_id']); ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['user_id']); ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['connection_type']); ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['connection_status']); ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['meter_number']); ?></td>
                <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['created_at']); ?></td>
                <td class="border border-gray-300 px-4 py-2">
                  <form method="POST">
                    <input type="hidden" name="connection_id" value="<?= $row['connection_id']; ?>">
                    <button type="submit" name="update_status" class="py-2 px-4 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Toggle Status</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>

</html>