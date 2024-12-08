<?php
session_start();
include './database.php';

// Check if the form was submitted for signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
  // Get form data
  $name = $_POST['name'];
  $email = $_POST['email'];
  $mobile = $_POST['mobile'];
  $address = $_POST['address'];
  $password = $_POST['password'];
  $connectionType = $_POST['connection_type'];
  $meterNumber = $_POST['meter_number'];

  // Check if the email exists in the admins table
  $adminCheckQuery = "SELECT * FROM admins WHERE email = ?";
  $stmt = $conn->prepare($adminCheckQuery);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  // If email exists in admins table, reject registration
  if ($result->num_rows > 0) {
    echo "<script>alert('This email is already associated with an admin account.');</script>";
  } else {
    // Insert user into the users table
    $conn->begin_transaction(); // Start a transaction for atomicity
    try {
      $userInsertQuery = "INSERT INTO users (name, email, phone_number, password, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($userInsertQuery);
      $stmt->bind_param("sssss", $name, $email, $mobile, $password, $address);

      if ($stmt->execute()) {
        $userId = $stmt->insert_id; // Get the newly created user ID

        // Insert connection details into the connections table
        $connectionInsertQuery = "INSERT INTO connections (user_id, connection_type, meter_number, connection_status, created_at) VALUES (?, ?, ?, 'Active', NOW())";
        $stmt = $conn->prepare($connectionInsertQuery);
        $stmt->bind_param("iss", $userId, $connectionType, $meterNumber);
        $stmt->execute();

        $conn->commit(); // Commit the transaction
        echo "<script>alert('Account and connection created successfully!');</script>";

        // Redirect to user dashboard
        header("Location: ./users/index.php");
        exit();
      } else {
        throw new Exception('Error creating account.');
      }
    } catch (Exception $e) {
      $conn->rollback(); // Rollback the transaction on failure
      echo "<script>alert('{$e->getMessage()} Please try again.');</script>";
    }
  }

  $stmt->close();
  $conn->close();
}

// Login logic
if (isset($_POST['login'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Check in the Admin table
  $query = "SELECT * FROM admins WHERE email = ? AND password = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ss", $email, $password);  // Only two parameters
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $_SESSION['admin_id'] = $row['admin_id'];
    $_SESSION['role'] = 'Admin';
    $_SESSION['name'] = $row['name'];
    $_SESSION['email'] = $row['email'];

    header('location: ./admin/admin.php');
    exit;
  }

  // Check in the Users table
  $query = "SELECT * FROM users WHERE email = ? AND password = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ss", $email, $password);  // Only two parameters
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['role'] = 'User';
    $_SESSION['name'] = $row['name'];
    $_SESSION['email'] = $row['email'];

    header('location: ./users/index.php');
    exit;
  }

  echo '<p class="small-text">Invalid email or password.</p>';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Online Electric Billings</title>
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.7/tailwind.min.css'>
</head>

<body>
  <!-- partial:index.partial.html -->
  <div class="flex min-h-screen mx-auto justify-center items-center bg-gray-50 py-12 px-4">
    <!-- Registration form -->
    <div class="registration-form max-w-md w-full space-y-8">
      <div>
        <h2 class="text-center text-3xl sm:text-3xl md:text-4xl font-bold text-gray-800">Create your account</h2>
        <p class="mt-1 text-center text-gray-500">Already Registered? <a id="login-link" href="#" class="text-blue-500 hover:underline">Sign in</a></p>
      </div>

      <form class="bg-white py-8 px-6 shadow rounded-lg mb-0 space-y-5" method="POST" action="">
        <div class="space-y-5 md:flex md:space-x-2 md:space-y-0">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <div class="mt-1">
              <input type="text" name="name" id="name" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
            </div>
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <div class="mt-1">
              <input type="text" name="address" id="address" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
            </div>
          </div>
        </div>

        <div>
          <label for="connection_type" class="block text-sm font-medium text-gray-700">Connection Type</label>
          <div class="mt-1">
            <select name="connection_type" id="connection_type" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
              <option value="Residential">Residential</option>
              <option value="Commercial">Commercial</option>
            </select>
          </div>
        </div>

        <div>
          <label for="meter_number" class="block text-sm font-medium text-gray-700">Meter Number</label>
          <div class="mt-1">
            <input type="text" name="meter_number" id="meter_number" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
          </div>
        </div>


        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
          <div class="mt-1">
            <input type="text" name="email" id="email" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
          </div>
        </div>

        <div>
          <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile No.</label>
          <div class="mt-1">
            <input type="number" name="mobile" id="mobile" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-1">
            <input type="password" name="password" id="password" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
          </div>
        </div>

        <div class="flex text-sm items-center space-x-1">
          <input id="termsAndPrivacy" name="termsAndPrivacy" type="checkbox" class="border border-gray-500 h-4 w-4 text-blue-600 rounded focus:ring-1 focus:ring-blue-500">
          <label for="termsAndPrivacy" class="text-gray-700">I agree to the <a href="#" class="text-blue-500 hover:text-blue-700">Terms</a> and <a href="#" class="text-blue-500 hover:text-blue-700">Privacy Policy</a></label>
        </div>

        <button type="submit" name="register" class="w-full px-4 py-2 text-center bg-blue-500 rounded border border-transparent shadow-sm text-white font-medium hover:bg-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-400">Sign Up</button>
      </form>
    </div>

    <!-- Login form -->
    <div class="login-form hidden max-w-md w-full space-y-8">
      <div>
        <h2 class="text-center text-3xl sm:text-3xl md:text-4xl font-bold text-gray-800">Log in to your account</h2>
        <p class="mt-1 text-center text-gray-500">Not Registered? <a id="register-link" href="#" class="text-blue-500 hover:underline">Sign up</a></p>
      </div>

      <form class="bg-white py-8 px-6 shadow rounded-lg mb-0 space-y-5" method="POST" action="">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
          <div class="mt-1">
            <input type="text" name="email" id="email" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-1">
            <input type="password" name="password" id="password" class="appearance-none px-3 py-2 w-full border border-gray-300 rounded shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
          </div>
        </div>

        <button type="submit" name="login" class="w-full px-4 py-2 text-center bg-blue-500 rounded border border-transparent shadow-sm text-white font-medium hover:bg-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-400">Log In</button>
      </form>
    </div>
  </div>

  <script>
    const loginLink = document.getElementById('login-link');
    const registerLink = document.getElementById('register-link');
    const loginForm = document.querySelector('.login-form');
    const registerForm = document.querySelector('.registration-form');

    loginLink.addEventListener('click', () => {
      loginForm.classList.remove('hidden');
      registerForm.classList.add('hidden');
    });

    registerLink.addEventListener('click', () => {
      registerForm.classList.remove('hidden');
      loginForm.classList.add('hidden');
    });
  </script>
</body>

</html>