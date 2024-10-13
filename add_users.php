<?php
// Start the session to verify if the user is logged in as admin
session_start();
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'admin') {
    // Redirect to login page if not logged in as admin
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
        header("Location: Users.php");
        exit();
    } else {
        // Insert the new user into the database
        $sql = "INSERT INTO user_management (username, email, password, user_type) 
                VALUES ('$username', '$email', '$hashed_password', '$user_type')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "New account created successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }

        // Redirect back to the Users page
        header("Location: Users.php");
        exit();
    }
}

// Close the connection
$conn->close();
?>
