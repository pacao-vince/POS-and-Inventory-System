<?php
header('Content-Type: application/json');

require_once '../includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE category SET category_name = ? WHERE category_id = ?");
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        // Send a success response in JSON format
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        // Send an error response in JSON format
        echo json_encode(['success' => false, 'message' => 'Error updating record: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
