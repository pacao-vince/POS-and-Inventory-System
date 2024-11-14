<?php
session_start();

// Set session timeout duration (e.g., 30 minutes)
$timeout_duration = 1800; // 30 minutes

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../public/login.php');
    exit();
}

// Check for session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: ../public/login.php');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
