<?php
session_start();
include '../database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

// Insert feedback into the database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
  $user_id = $_SESSION['user_id'];  // Assume user is logged in and user_id is stored in session
  $feedback_text = $_POST['feedback_text'];
  $rating = $_POST['rating'] ?? null; // Rating is optional

  $insertQuery = "INSERT INTO feedback (user_id, feedback_text, rating) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($insertQuery);
  $stmt->bind_param("iss", $user_id, $feedback_text, $rating); // Bind parameters
  if ($stmt->execute()) {
    echo "<script>alert('Feedback submitted successfully.'); window.location.href='feedback.php';</script>";
  } else {
    echo "<script>alert('Error submitting feedback.'); window.location.href='feedback.php';</script>";
  }
}

// Fetch feedback for display
$feedbackQuery = "SELECT feedback.feedback_id, feedback.feedback_text, feedback.rating, feedback.submitted_at, users.name FROM feedback INNER JOIN users ON feedback.user_id = users.user_id ORDER BY feedback.submitted_at DESC";
$feedbackResult = $conn->query($feedbackQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body class="bg-gray-100">
  <div class="min-h-screen flex">
    <!-- Sidebar -->
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
    <div class="flex-1 p-8 pl-80">
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Submit Feedback</h1>

      <!-- Feedback Form -->
      <form action="feedback.php" method="POST" class="bg-white shadow p-6 rounded-lg">
        <div class="mb-4">
          <label for="feedback_text" class="block text-gray-700">Feedback</label>
          <textarea id="feedback_text" name="feedback_text" rows="4" class="w-full p-2 border border-gray-300 rounded-lg" required></textarea>
        </div>
        <div class="mb-4">
          <label for="rating" class="block text-gray-700">Rating</label>
          <input type="number" id="rating" name="rating" min="1" max="5" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Optional" />
        </div>
        <div>
          <button type="submit" name="submit_feedback" class="bg-blue-500 text-white py-2 px-4 rounded-lg">Submit Feedback</button>
        </div>
      </form>

      <!-- Display Feedback -->
      <h2 class="text-2xl font-semibold text-gray-800 mt-8">Previous Feedback</h2>
      <table class="min-w-full bg-white shadow rounded-lg mt-4">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">User</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Feedback</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Rating</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Submitted At</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($feedback = $feedbackResult->fetch_assoc()): ?>
            <tr>
              <td class="px-4 py-2"><?php echo htmlspecialchars($feedback['name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
              <td class="px-4 py-2"><?php echo $feedback['rating'] ? htmlspecialchars($feedback['rating']) : 'No rating'; ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($feedback['submitted_at']); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>