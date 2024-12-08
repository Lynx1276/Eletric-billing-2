<?php
include '../database.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Fetch the connection ID for the selected user
    $query = "SELECT connection_id FROM connections WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $connection = $result->fetch_assoc();
        echo json_encode(['connection_id' => $connection['connection_id']]);
    } else {
        echo json_encode(['error' => 'No connection found for the selected user.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
