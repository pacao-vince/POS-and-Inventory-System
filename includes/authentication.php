<?php
session_start();
include '../includes/db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for connection issues
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection error.']);
        exit;
    }

    // Capture and sanitize inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Query to get the hashed password for the given username from the user_management table
    $query = "SELECT password FROM user_management WHERE username = ? AND user_type = 'admin'";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            // Verify the entered password against the hashed password
            if (password_verify($password, $hashedPassword)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password. Please try again.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username. Please try again.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
