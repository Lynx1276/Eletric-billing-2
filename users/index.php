<?php
session_start();
include '../database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch user-specific data from the database

// Total Billing
$user_id = $_SESSION['user_id'];
$totalBillingQuery = "SELECT SUM(total_cost) AS total_billing FROM billing WHERE user_id = ?";
$stmt = $conn->prepare($totalBillingQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalBilling);
$stmt->fetch();
$stmt->close();

// Unresolved Complaints
$unresolvedComplaintsQuery = "SELECT COUNT(*) AS unresolved FROM complaints WHERE user_id = ? AND status = 'unresolved'";
$stmt = $conn->prepare($unresolvedComplaintsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($unresolvedComplaints);
$stmt->fetch();
$stmt->close();

// Payments Made
$totalPaymentsQuery = "SELECT SUM(amount_paid) AS amount_paid FROM payments WHERE bill_id = ?";
$stmt = $conn->prepare($totalPaymentsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalPayments);
$stmt->fetch();
$stmt->close();

// Fetch Latest Feedback
$latestFeedbackQuery = "SELECT feedback_text, submitted_at FROM feedback WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1";
$stmt = $conn->prepare($latestFeedbackQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latestFeedbackResult = $stmt->get_result();
$latestFeedback = $latestFeedbackResult->fetch_assoc();
$stmt->close();

// Fetch Latest Complaint
$latestComplaintQuery = "SELECT complaint_details, created_at FROM complaints WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($latestComplaintQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latestComplaintResult = $stmt->get_result();
$latestComplaint = $latestComplaintResult->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body class="bg-gray-100">
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
        <hr class="text-lg bg-black">
        <nav class="mt-10">
            <ul class="space-y-4 text-center">
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
    <div class="ml-72 p-10">
        <!-- Dashboard Header -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-blue-500 mb-5">Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p class="text-gray-700">Use the sidebar to navigate to different sections of the dashboard.</p>
        </div>

        <!-- Quick Actions Section -->
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 shadow-md rounded-lg text-center">
                <h3 class="text-xl font-semibold mb-3">Submit Feedback</h3>
                <p class="text-gray-500 mb-4">Let us know your thoughts about our services.</p>
                <a href="feedback.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg">Give Feedback</a>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg text-center">
                <h3 class="text-xl font-semibold mb-3">View Billing</h3>
                <p class="text-gray-500 mb-4">Check your past and current billing details.</p>
                <a href="billing.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg">View Billing</a>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg text-center">
                <h3 class="text-xl font-semibold mb-3">Raise a Complaint</h3>
                <p class="text-gray-500 mb-4">Have an issue? Report it here.</p>
                <a href="complaints.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg">Raise Complaint</a>
            </div>
        </div>

        <!-- Key Metrics Section -->
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Total Billing</h3>
                <p class="text-gray-700">$<?= number_format($totalBilling, 2); ?></p>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Unresolved Complaints</h3>
                <p class="text-gray-700"><?= $unresolvedComplaints; ?></p>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Payments Made</h3>
                <p class="text-gray-700">$<?= number_format($totalPayments, 2); ?></p>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <!-- Recent Feedback and Complaints Section -->
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Latest Feedback</h3>
                <?php if ($latestFeedback): ?>
                    <p class="text-gray-700"><?= htmlspecialchars($latestFeedback['feedback_text']); ?></p>
                    <p class="text-sm text-gray-500">Submitted on: <?= date('M d, Y', strtotime($latestFeedback['submitted_at'])); ?></p>
                <?php else: ?>
                    <p class="text-gray-500">No feedback submitted yet.</p>
                <?php endif; ?>
            </div>
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-xl font-semibold mb-3">Latest Complaint</h3>
                <?php if ($latestComplaint): ?>
                    <p class="text-gray-700"><?= htmlspecialchars($latestComplaint['complaint_details']); ?></p>
                    <p class="text-sm text-gray-500">Filed on: <?= date('M d, Y', strtotime($latestComplaint['created_at'])); ?></p>
                <?php else: ?>
                    <p class="text-gray-500">No complaints filed yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>