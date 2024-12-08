<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../index.php");
  exit();
}

include '../database.php';

// Fetch feedback
$sql = "SELECT feedback.feedback_id, users.name AS name, feedback.feedback_text, feedback.rating, feedback.submitted_at 
        FROM feedback 
        JOIN users ON feedback.user_id = users.user_id
        ORDER BY feedback.submitted_at DESC";
$result = $conn->query($sql);
$feedbacks = $result->fetch_all(MYSQLI_ASSOC);

// Handle delete feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
  $feedback_id = $_POST['feedback_id'];
  $delete_sql = "DELETE FROM feedback WHERE feedback_id = ?";
  $stmt = $conn->prepare($delete_sql);
  $stmt->bind_param("i", $feedback_id);
  $stmt->execute();
  header("Location: feedback.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Electric Billings</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body class="bg-gray-100">
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
      <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
      <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
      <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
    </ul>
  </div>

  <div class="ml-64 p-10">
    <div class="bg-white shadow-md rounded-lg p-6">
      <h1 class="text-2xl font-bold text-blue-500 mb-5">User Feedback</h1>
      <table class="min-w-full bg-white shadow rounded-lg">
        <thead>
          <tr>
            <th class="p-3">User Name</th>
            <th class="p-3">Feedback</th>
            <th class="p-3">Rating</th>
            <th class="p-3">Submitted At</th>
            <th class="p-3">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($feedbacks as $feedback): ?>
            <tr class="border-b">
              <td class="p-3 text-center"><?= htmlspecialchars($feedback['name']) ?></td>
              <td class="p-3 text-center"><?= htmlspecialchars($feedback['feedback_text']) ?></td>
              <td class="p-3 text-center"><?= htmlspecialchars($feedback['rating']) ?? 'No Rating' ?></td>
              <td class="p-3 text-center"><?= htmlspecialchars($feedback['submitted_at']) ?></td>
              <td class="p-3 text-center">
                <form method="POST" action="feedback.php" class="inline">
                  <input type="hidden" name="feedback_id" value="<?= $feedback['feedback_id'] ?>">
                  <button type="submit" name="delete_feedback" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>