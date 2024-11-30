<?php

require_once '../includes/db_connection.php';

// Fetch user details
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT user_id, username, email, user_type FROM user_management WHERE user_id=?");
    $stmt->bind_param("i", $user_id); 

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Output user details as JSON
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["Error" => "User not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
