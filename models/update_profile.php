<?php
// update_profile.php
session_start();
require '../includes/db_connection.php'; // Include your database connection file

header('Content-Type: application/json'); // Ensure response is in JSON format

$response = ['success' => false, 'message' => 'An error occurred.']; // Default response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;

    // Validate input
    if (!$username || !$email) {
        $response['message'] = 'Username or email is missing.';
        echo json_encode($response);
        exit;
    }

    // Get the user ID from the session
    $userId = $_SESSION['user_id']; // Adjust this to get the user ID from the session or login
    
    // Prepare and execute the SQL query to update user profile
    $sql = "UPDATE user_management SET username = ?, email = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $email, $userId);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username; // Update session with the new username
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully!' // Success message
        ];
    } else {
        $response['message'] = 'Error updating profile';
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response); // Send response back to the frontend
?>
