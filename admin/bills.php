<?php
session_start();
include '../database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}

// Fetch all users for dropdown in the Add Bill form
$usersQuery = "SELECT user_id, name FROM users";
$usersResult = $conn->query($usersQuery);

// Fetch bills based on search/filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$billsQuery = "
SELECT billing.*, users.name AS name
FROM billing 
JOIN users ON billing.user_id = users.user_id
WHERE (users.name LIKE ? OR billing.user_id LIKE ?)
" . ($filter ? "AND billing.bill_status = ?" : "");

$stmt = $conn->prepare($billsQuery);
if ($filter) {
  $stmt->bind_param("sss", $searchTerm, $searchTerm, $filter);
} else {
  $stmt->bind_param("ss", $searchTerm, $searchTerm);
}
$searchTerm = '%' . $search . '%';
$stmt->execute();
$billsResult = $stmt->get_result();
$stmt->close();

function addBill($user_id, $connection_id, $billing_month, $cost_per_unit)
{
  global $conn;

  $meterQuery = "SELECT * FROM meters WHERE connection_id = ? ORDER BY reading_date DESC LIMIT 1";
  $stmt = $conn->prepare($meterQuery);
  $stmt->bind_param("i", $connection_id);
  if (!$stmt->execute()) {
    die("Meter query failed: " . $stmt->error);
  }

  $meterResult = $stmt->get_result();
  $stmt->close();

  $meterData = $meterResult->fetch_assoc();
  if (!$meterData) {
    die("No meter readings found for connection ID: " . $connection_id);
  }

  $units_consumed = $meterData['current_reading'] - $meterData['previous_reading'];
  $total_cost = $units_consumed * $cost_per_unit;

  $insertBillQuery = "
      INSERT INTO billing (user_id, connection_id, billing_month, units_consumed, cost_per_unit, total_cost, bill_status, due_date) 
      VALUES (?, ?, ?, ?, ?, ?, 'Unpaid', DATE_ADD(NOW(), INTERVAL 30 DAY))
  ";
  $stmt = $conn->prepare($insertBillQuery);
  $stmt->bind_param("iisdd", $user_id, $connection_id, $billing_month, $units_consumed, $cost_per_unit, $total_cost);

  if (!$stmt->execute()) {
    die("Insert bill failed: " . $stmt->error);
  }
  $stmt->close();
  echo "Bill added successfully!";
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
        <li><a href="admin.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 transition duration-200">Dashboard</a></li>
        <li><a href="users.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800  hover:bg-gray-200 transition duration-200">Manage Users</a></li>
        <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Meters</a></li>
        <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200 hover:bg-gray-700 transition duration-200">Manage Bills</a></li>
        <li><a href="connections.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Connections</a></li>
        <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
        <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
        <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
        <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
        <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 pl-80">
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Manage Bills</h1>

      <!-- Search and Filter Form -->
      <form method="GET" class="flex items-center mb-6">
        <input type="text" name="search" placeholder="Search Bills (Name or ID)" class="p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-1/3" value="<?= htmlspecialchars($search); ?>">
        <select name="filter" class="p-3 ml-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">All</option>
          <option value="Paid" <?= $filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
          <option value="Unpaid" <?= $filter == 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
        </select>
        <button type="submit" class="ml-4 py-2 px-4 bg-blue-700 text-white rounded-lg hover:bg-blue-800">Apply</button>
      </form>

      <!-- Bills Table -->
      <table class="min-w-full bg-white shadow-lg rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Bill ID</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">User Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Total Cost</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Billing Month</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Bill Status</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Due Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($bill = $billsResult->fetch_assoc()): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= $bill['bill_id']; ?></td>
              <td class="px-4 py-2"><?= $bill['name']; ?></td>
              <td class="px-4 py-2"><?= $bill['total_cost']; ?></td>
              <td class="px-4 py-2"><?= $bill['billing_month']; ?></td>
              <td class="px-4 py-2"><?= $bill['bill_status']; ?></td>
              <td class="px-4 py-2"><?= $bill['due_date']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const addBillBtn = document.getElementById('addBillBtn');
    const addBillForm = document.getElementById('addBillForm');

    addBillBtn.addEventListener('click', () => {
      addBillForm.classList.toggle('hidden');
    });
  </script>
</body>

</html>