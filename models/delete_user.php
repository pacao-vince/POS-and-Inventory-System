<?php
session_start();
include '../includes/db_connection.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Archive the user (update archived column)
    $archive_sql = "UPDATE user_management SET archived = 1 WHERE user_id = ?";
    $archive_stmt = $conn->prepare($archive_sql);
    $archive_stmt->bind_param("i", $user_id);

    if ($archive_stmt->execute()) {
        // Archiving was successful
        echo json_encode(["success" => true, "message" => "User archived successfully."]);
    } else {
        // Archive failed
        echo json_encode(["success" => false, "message" => "Error archiving user: " . $conn->error]);
    }

    $archive_stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "User ID is missing."]);
}

$conn->close();
?>
