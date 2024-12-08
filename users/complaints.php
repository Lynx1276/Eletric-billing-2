<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../database.php';
$user_id = $_SESSION['user_id'];

// Add complaint functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_complaint'])) {
    $complaint_type = $_POST['complaint_type'];
    $complaint_details = $_POST['complaint_details'];

    // Validate inputs
    if (!empty($complaint_type) && !empty($complaint_details)) {
        $sql = "INSERT INTO complaints (user_id, complaint_type, complaint_details, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $complaint_type, $complaint_details);
        if ($stmt->execute()) {
            $message = "Complaint added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

// Fetch existing complaints
$sql = "SELECT * FROM complaints WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body class="bg-gray-100">
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

    <div class="ml-64 p-10">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-2xl font-bold text-blue-500 mb-5">Your Complaints</h1>

            <!-- Display Message -->
            <?php if (isset($message)): ?>
                <div class="bg-green-500 text-white p-3 rounded mb-5">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Complaint Submission Form -->
            <form action="complaints.php" method="POST" class="mb-6">
                <div class="mb-4">
                    <label for="complaint_type" class="block text-gray-700">Complaint Type</label>
                    <input type="text" name="complaint_type" id="complaint_type" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label for="complaint_details" class="block text-gray-700">Complaint Details</label>
                    <textarea name="complaint_details" id="complaint_details" class="w-full px-3 py-2 border rounded" rows="4" required></textarea>
                </div>
                <button type="submit" name="submit_complaint" class="bg-blue-500 text-white px-6 py-2 rounded">Submit Complaint</button>
            </form>

            <!-- Complaints Table -->
            <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-red-500 text-white">
                    <tr>
                        <th class="p-3">Type</th>
                        <th class="p-3">Details</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr class="border-b">
                            <td class="p-3 text-center"><?= htmlspecialchars($complaint['complaint_type']) ?></td>
                            <td class="p-3 text-center"><?= htmlspecialchars($complaint['complaint_details']) ?></td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 rounded <?= $complaint['status'] == 'Resolved' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' ?>">
                                    <?= htmlspecialchars($complaint['status']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-center"><?= htmlspecialchars($complaint['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>