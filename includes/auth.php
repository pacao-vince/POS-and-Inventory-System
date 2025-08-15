<?php
session_start();

// Set session timeout duration (e.g., 30 minutes)
$timeout_duration = 1800; // 30 minutes

// Function to check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// Function to check for session timeout and reset last activity time
function checkSessionTimeout() {
    global $timeout_duration;
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        logout(); // Call the logout function on timeout
    } else {
        $_SESSION['last_activity'] = time(); // Update last activity timestamp
    }
}

// Function to handle user logout
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Run session checks if user is logged in
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
} else {
    checkSessionTimeout();
}
?>
