<?php
header('Content-Type: application/json');
require_once '../includes/db_connection.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

    // Password hashing for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_user_sql = "SELECT * FROM user_management WHERE username = '$username' OR email = '$email'";
    $check_user_result = $conn->query($check_user_sql);

    if ($check_user_result->num_rows > 0) {
        // Username or email already exists
        $_SESSION['error'] = "Username or email already exists!";
        header("Location: ../views/Users.php");
        exit();
    } else {
        try {
            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO user_management (username, email, password, user_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $user_type);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'New account created successfully!']);
            } else {
                // If there was an error executing the query
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }

            // Close the statement and connection
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            // Catch any unexpected exceptions
            echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }
} else {
    // If it's not a POST request, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

exit;
?>
