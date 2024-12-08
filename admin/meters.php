<?php
session_start();
include '../database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}

// Fetch all users for dropdown in the Add Meter form
$usersQuery = "SELECT user_id, name FROM users";
$usersResult = $conn->query($usersQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'];
  $connection_id = $_POST['connection_id'];
  $reading_date = $_POST['reading_date'];
  $current_reading = $_POST['current_reading'];
  $previous_reading = $_POST['previous_reading'];
  $admin_id = $_SESSION['admin_id'];

  // Validate admin_id
  $adminCheckQuery = "SELECT admin_id FROM admins WHERE admin_id = ?";
  $stmt = $conn->prepare($adminCheckQuery);
  $stmt->bind_param("i", $admin_id);
  $stmt->execute();
  $adminCheckResult = $stmt->get_result();
  $stmt->close();

  if ($adminCheckResult->num_rows === 0) {
    echo "<script>alert('Invalid admin ID. Please check your login credentials.');</script>";
    exit;
  }

  // Validate user_id
  $userCheckQuery = "SELECT user_id FROM users WHERE user_id = ?";
  $stmt = $conn->prepare($userCheckQuery);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $userCheckResult = $stmt->get_result();
  $stmt->close();

  if ($userCheckResult->num_rows === 0) {
    echo "<script>alert('Invalid user ID. Please select a valid user.');</script>";
    exit;
  }

  // Fetch connection details
  $connectionQuery = "SELECT connection_type FROM connections WHERE connection_id = ?";
  $stmt = $conn->prepare($connectionQuery);
  $stmt->bind_param("i", $connection_id);
  $stmt->execute();
  $connectionResult = $stmt->get_result();
  $stmt->close();

  if ($connectionResult->num_rows === 0) {
    echo "<script>alert('Invalid connection ID. Please select a valid connection.');</script>";
    exit;
  }

  $connection = $connectionResult->fetch_assoc();
  $connection_type = $connection['connection_type'];

  // Calculate units consumed
  $units_consumed = $current_reading - $previous_reading;

  if ($units_consumed < 0) {
    echo "<script>alert('Current reading must be greater than or equal to the previous reading.');</script>";
    exit;
  }

  // Fetch cost per unit based on connection type
  $cost_per_unit = ($connection_type === 'Commercial') ? 7.00 : 5.00;

  // Calculate total cost
  $total_cost = $units_consumed * $cost_per_unit;

  // Begin transaction
  $conn->begin_transaction();

  try {
    // Insert the meter record
    $insertMeterQuery = "
            INSERT INTO meters (connection_id, reading_date, current_reading, previous_reading, admin_id) 
            VALUES (?, ?, ?, ?, ?)
        ";
    $stmt = $conn->prepare($insertMeterQuery);
    $stmt->bind_param("issdi", $connection_id, $reading_date, $current_reading, $previous_reading, $admin_id);

    if (!$stmt->execute()) {
      throw new Exception("Failed to insert meter reading: " . $stmt->error);
    }

    // Get the inserted meter ID
    $meter_id = $stmt->insert_id;

    // Calculate the billing month and due date
    $billing_month = date('Y-m-d', strtotime($reading_date)); // Extract year and month
    $due_date = date('Y-m-d', strtotime('+30 days', strtotime($reading_date))); // Add 30 days for due date

    // Insert the billing record
    $insertBillingQuery = "
    INSERT INTO billing (
        user_id, connection_id, admin_id, meter_id, 
        billing_month, units_consumed, cost_per_unit, due_date, bill_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
";
    $stmt = $conn->prepare($insertBillingQuery);
    if (!$stmt) {
      throw new Exception("Failed to prepare billing query: " . $conn->error);
    }

    $bill_status = 'Unpaid'; // Default status

    // Bind parameters to the statement
    $stmt->bind_param(
      "iiiisddss", // Parameter types: i=int, s=string, d=double
      $user_id,
      $connection_id,
      $admin_id,
      $meter_id,
      $billing_month,  // This is a string (Y-m format)
      $units_consumed,
      $cost_per_unit,
      $due_date,       // This is a string (Y-m-d format)
      $bill_status     // This is a string
    );

    // Execute the statement
    if (!$stmt->execute()) {
      throw new Exception("Failed to insert billing record: " . $stmt->error);
    }

    echo "Billing record inserted successfully!";
    echo "Billing Month: $billing_month, Due Date: $due_date";



    // Commit transaction
    $conn->commit();

    echo "<script>alert('Meter reading and bill added successfully!'); window.location.href = 'meters.php';</script>";
  } catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    echo "<script>alert('Transaction failed: " . $e->getMessage() . "');</script>";
  } finally {
    $stmt->close();
  }
}

              // Fetch all meter records
              $metersQuery = "
    SELECT 
        m.meter_id, 
        u.name AS user_name, 
        c.connection_type, 
        m.reading_date, 
        m.current_reading, 
        m.previous_reading, 
        a.name AS admin_name
    FROM 
        meters m
    JOIN 
        users u ON m.connection_id = u.user_id
    JOIN 
        connections c ON m.connection_id = c.connection_id
    JOIN 
        admins a ON m.admin_id = a.admin_id
        
";
              $metersResult = $conn->query($metersQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Online Meter Management</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-100">
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <div class="bg-white text-black p-6 shadow-lg w-72 fixed h-full">
      <h2 class="text-2xl font-semibold text-gray-800 mb-8">Online Meter Management | Admin Dashboard</h2>
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
        <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200">Manage Meters</a></li>
        <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Bills</a></li>
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
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Manage Meters</h1>

      <!-- Add Meter Button -->
      <button id="addMeterBtn" class="py-2 px-4 bg-green-700 text-white rounded-lg hover:bg-green-800 mb-6">Add Meter Reading</button>

      <!-- Add Meter Form -->
      <div id="addMeterForm" class="p-6 bg-white rounded-lg shadow-lg hidden">
        <form method="POST">
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add Meter Reading</h2>
          <div class="mb-4">
            <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
            <select name="user_id" id="user_id" required class="p-3 border border-gray-300 rounded-lg w-full">
              <option value="">Select User</option>
              <?php while ($user = $usersResult->fetch_assoc()): ?>
                <option value="<?= $user['user_id']; ?>"><?= $user['name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-4">
            <label for="connection_id" class="block text-sm font-medium text-gray-700">Connection ID</label>
            <input type="number" name="connection_id" id="connection_id" required class="p-3 border border-gray-300 rounded-lg w-full">
          </div>
          <div class="mb-4">
            <label for="reading_date" class="block text-sm font-medium text-gray-700">Reading Date</label>
            <input type="date" name="reading_date" id="reading_date" required class="p-3 border border-gray-300 rounded-lg w-full">
          </div>
          <div class="mb-4">
            <label for="current_reading" class="block text-sm font-medium text-gray-700">Current Reading</label>
            <input type="number" name="current_reading" id="current_reading" required class="p-3 border border-gray-300 rounded-lg w-full">
          </div>
          <div class="mb-4">
            <label for="previous_reading" class="block text-sm font-medium text-gray-700">Previous Reading</label>
            <input type="number" name="previous_reading" id="previous_reading" required class="p-3 border border-gray-300 rounded-lg w-full">
          </div>
          <button type="submit" class="py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Submit Reading</button>
        </form>
      </div>

      <!-- Meter Records Table -->
      <div class="mt-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Meter Records</h2>
        <div class="overflow-auto bg-white rounded-lg shadow">
          <table class="min-w-full bg-white">
            <thead class="bg-gray-200">
              <tr>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Meter ID</th>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">User Name</th>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Connection Type</th>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Reading Date</th>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Current Reading</th>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Previous Reading</th>
                <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Admin</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($metersResult->num_rows > 0): ?>
                <?php while ($meter = $metersResult->fetch_assoc()): ?>
                  <tr class="border-t">
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['meter_id']; ?></td>
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['user_name']; ?></td>
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['connection_type']; ?></td>
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['reading_date']; ?></td>
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['current_reading']; ?></td>
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['previous_reading']; ?></td>
                    <td class="py-2 px-4 text-sm text-gray-700"><?= $meter['admin_name']; ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="py-2 px-4 text-center text-sm text-gray-600">No meter records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <script>
    document.getElementById('addMeterBtn').addEventListener('click', function() {
      document.getElementById('addMeterForm').classList.toggle('hidden');
    });

    $(document).ready(function() {
      $('#user_id').change(function() {
        const userId = $(this).val();

        if (userId) {
          $.ajax({
            url: 'fetch_connection.php',
            method: 'POST',
            data: {
              user_id: userId
            },
            dataType: 'json',
            success: function(response) {
              if (response.connection_id) {
                $('#connection_id').val(response.connection_id);
              } else {
                alert(response.error || 'Error fetching connection ID.');
                $('#connection_id').val('');
              }
            },
            error: function() {
              alert('Failed to fetch connection ID. Please try again.');
              $('#connection_id').val('');
            }
          });
        } else {
          $('#connection_id').val('');
        }
      });
    });
  </script>
</body>

</html>