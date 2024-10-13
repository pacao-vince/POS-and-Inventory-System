<?php
session_start(); // Start the session

// Check if the user is logging out
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy all session data
    $_SESSION = array(); // Clear the session array

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy(); // Destroy the session

    // Redirect to the login page or home page
    header('Location: login.php');
    exit;
}

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // User is not logged in, redirect to login page
    header('Location: login.php');
    exit;
}
?>