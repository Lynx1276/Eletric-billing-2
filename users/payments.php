<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../database.php';
$user_id = $_SESSION['user_id'];

// Fetch user's bills and payment history
$sql = "SELECT billing.bill_id, billing.billing_month, billing.total_cost, billing.bill_status 
        FROM billing 
        WHERE billing.connection_id IN (
            SELECT connection_id FROM connections WHERE user_id = ?
        )";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bills = $result->fetch_all(MYSQLI_ASSOC);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    $bill_id = $_POST['bill_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_method = $_POST['payment_method'];

    // Insert payment into the payments table
    $payment_sql = "INSERT INTO payments (bill_id, payment_date, payment_method, amount_paid) 
                    VALUES (?, NOW(), ?, ?)";
    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("isi", $bill_id, $payment_method, $amount_paid);
    $payment_stmt->execute();

    // Update the bill status to 'Paid'
    $update_sql = "UPDATE billing SET bill_status = 'Paid' WHERE bill_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $bill_id);
    $update_stmt->execute();

    // Redirect to the same page to show updated information
    header("Location: payments.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
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
            <h1 class="text-2xl font-bold text-blue-500 mb-5">Payment History</h1>
            <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-green-500 text-white">
                    <tr>
                        <th class="p-3">Billing Month</th>
                        <th class="p-3">Bill Status</th>
                        <th class="p-3">Total Cost</th>
                        <th class="p-3">Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bills as $bill): ?>
                        <tr class="border-b">
                            <td class="p-3 text-center"><?= htmlspecialchars($bill['billing_month']) ?></td>
                            <td class="p-3 text-center"><?= htmlspecialchars($bill['bill_status']) ?></td>
                            <td class="p-3 text-center">₱<?= htmlspecialchars($bill['total_cost']) ?></td>
                            <td class="p-3 text-center">
                                <?php if ($bill['bill_status'] === 'Unpaid'): ?>
                                    <button class="bg-blue-500 text-white px-4 py-2 rounded"
                                        onclick="document.getElementById('payment-form-<?= $bill['bill_id'] ?>').style.display = 'block'">
                                        Pay Now
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Payment Form for each bill -->
        <?php foreach ($bills as $bill): ?>
            <div id="payment-form-<?= $bill['bill_id'] ?>" class="payment-form" style="display:none; margin-top: 20px;">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-bold">Pay for Bill #<?= $bill['bill_id'] ?></h2>
                    <form method="POST" action="payments.php">
                        <input type="hidden" name="bill_id" value="<?= $bill['bill_id'] ?>">
                        <label for="payment_method" class="block text-lg font-medium mt-4">Payment Method:</label>
                        <select name="payment_method" id="payment_method" class="w-full p-2 mt-2 border rounded">
                            <option value="Gcash">Gcash</option>
                            <option value="Maya">Maya</option>
                            <option value="Paypal">Paypal</option>
                        </select>
                        <label for="amount_paid" class="block text-lg font-medium mt-4">Amount Paid (₱):</label>
                        <input type="number" name="amount_paid" id="amount_paid" class="w-full p-2 mt-2 border rounded" required>
                        <button type="submit" name="pay" class="mt-4 bg-green-500 text-white px-4 py-2 rounded">
                            Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>