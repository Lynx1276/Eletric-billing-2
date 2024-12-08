<?php
session_start();
include '../database.php';

// Check if the admin is logged in
$admin = isset($_SESSION['admin_id']);

// Fetch all users
$userQuery = "SELECT * FROM users";
$userResult = $conn->query($userQuery);

// Handle adding a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addUser'])) {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];

  $addUserQuery = "INSERT INTO users (name, email, phone_number) VALUES ('$name', '$email', '$phone')";
  if ($conn->query($addUserQuery)) {
    $_SESSION['message'] = 'User added successfully';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  } else {
    $_SESSION['error'] = 'Error adding user: ' . $conn->error;
  }
}

// Handle editing a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editUser'])) {
  $userId = $_POST['userId'];
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];

  $editUserQuery = "UPDATE users SET name='$name', email='$email', phone_number='$phone' WHERE id='$userId'";
  if ($conn->query($editUserQuery)) {
    $_SESSION['message'] = 'User updated successfully';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
  } else {
    $_SESSION['error'] = 'Error updating user: ' . $conn->error;
  }
}

// Fetch user data for editing
if (isset($_GET['edit'])) {
  $userId = $_GET['edit'];
  $editQuery = "SELECT * FROM users WHERE user_id = '$userId'";
  $editResult = $conn->query($editQuery);
  $editUser = $editResult->fetch_assoc();
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
        <li><a href="users.php" class="block px-4 py-2 mb-2 rounded-lg text-white bg-gray-600 hover:bg-gray-200 transition duration-200">Manage Users</a></li>
        <li><a href="meters.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Meters</a></li>
        <li><a href="bills.php" class="block px-4 py-2 mb-2 rounded-lg text-gary-800 hover:bg-gray-200 transition duration-200">Manage Bills</a></li>
        <li><a href="connections.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200">Manage Connections</a></li>
        <li><a href="tariffs.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Tariffs</a></li>
        <li><a href="complaints.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Complaints</a></li>
        <li><a href="feedback.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Manage Feedback</a></li>
        <li><a href="reports.php" class="block px-4 py-2 mb-2 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Reports</a></li>
        <li><a href="../logout.php" class="block px-4 py-2 mt-6 rounded-lg text-gray-800 hover:bg-gray-200 transition duration-200">Logout</a></li>
      </ul>
    </div>

    <div class="flex-1 p-8 pl-80">
      <h1 class="text-3xl font-semibold text-gray-800 mb-6">Manage Users</h1>

      <?php if (isset($_SESSION['message'])): ?>
        <div class="bg-green-200 p-4 rounded mb-4 text-green-800">
          <?php echo $_SESSION['message'];
          unset($_SESSION['message']); ?>
        </div>
      <?php elseif (isset($_SESSION['error'])): ?>
        <div class="bg-red-200 p-4 rounded mb-4 text-red-800">
          <?php echo $_SESSION['error'];
          unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <!-- Add/Edit User Form -->
      <form action="" method="POST" class="bg-white p-6 rounded shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4"><?php echo isset($editUser) ? 'Edit User' : 'Add New User'; ?></h2>

        <input type="hidden" name="userId" value="<?php echo $editUser['id'] ?? ''; ?>" />

        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
          <input type="text" name="name" id="name" class="w-full mt-1 p-2 border border-gray-300 rounded" value="<?php echo $editUser['name'] ?? ''; ?>" required />
        </div>

        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" name="email" id="email" class="w-full mt-1 p-2 border border-gray-300 rounded" value="<?php echo $editUser['email'] ?? ''; ?>" required />
        </div>

        <div class="mb-4">
          <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
          <input type="text" name="phone" id="phone" class="w-full mt-1 p-2 border border-gray-300 rounded" value="<?php echo $editUser['phone_number'] ?? ''; ?>" required />
        </div>

        <div class="flex justify-end">
          <button type="submit" name="<?php echo isset($editUser) ? 'editUser' : 'addUser'; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
            <?php echo isset($editUser) ? 'Update User' : 'Add User'; ?>
          </button>
        </div>
      </form>

      <!-- Users Table -->
      <table class="min-w-full bg-white shadow rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left">Name</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Phone</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($user = $userResult->fetch_assoc()): ?>
            <tr>
              <td class="px-4 py-2"><?php echo $user['name']; ?></td>
              <td class="px-4 py-2"><?php echo $user['email']; ?></td>
              <td class="px-4 py-2"><?php echo $user['phone_number']; ?></td>
              <td class="px-4 py-2">
                <a href="?edit=<?php echo $user['user_id']; ?>" class="text-blue-600 hover:text-blue-800">Edit</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>