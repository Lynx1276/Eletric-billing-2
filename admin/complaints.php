<?php
session_start();
include '../database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch complaints with associated user information
$complaintsQuery = "SELECT complaints.*, users.name FROM complaints INNER JOIN users ON complaints.user_id = users.user_id";
$complaintsResult = $conn->query($complaintsQuery);


// Resolve complaint action
if (isset($_GET['complaints_id'])) {
  $complaint_id = $_GET['complaints_id'];
  $resolveQuery = "UPDATE complaints SET status = 'Open', 'In Progress', 'Ressolved' WHERE complaint_id = ?";
  $stmt = $conn->prepare($resolveQuery);
  $stmt->bind_param("i", $complaint_id);
  if ($stmt->execute()) {
    echo "<script>alert('Complaint marked as resolved.'); window.location.href='complaints.php';</script>";
  } else {
    echo "<script>alert('Error resolving complaint.'); window.location.href='complaints.php';</script>";
  }
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
      <!-- Navigation Links -->
      <ul>
        <li><a href="admin.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-700 transition duration-200">Dashboard</a></li>
        <li><a href="users.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800  hover:bg-gray-200 transition duration-200">Manage Users</a></li>
        <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Meters</a></li>
        <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Bills</a></li>
        <li><a href="connections.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Connections</a></li>
        <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
        <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
        <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
        <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
        <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 pl-80"> <!-- Added padding-left to offset the sidebar -->
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Manage Complaints</h1>
      <table class="min-w-full bg-white shadow rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Complaint ID</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">User Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Complaint Type</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Complaint</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($complaint = $complaintsResult->fetch_assoc()): ?>
            <tr>
              <td class="px-4 py-2"><?php echo htmlspecialchars($complaint['complaint_id']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($complaint['name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($complaint['complaint_type']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($complaint['complaint_details']); ?></td>
              <td class="px-4 py-2"><?php echo $complaint['status'] == 1 ? 'Resolved' : 'Pending'; ?></td>
              <td class="px-4 py-2">
                <?php if ($complaint['status'] == 0): ?>
                  <a href="complaints.php?id=<?php echo $complaint['complaint_id']; ?>" class="text-green-500 hover:underline">Mark as Resolved</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>