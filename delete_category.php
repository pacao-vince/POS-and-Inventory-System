<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Set JSON response header
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM category WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);

    // Execute statement and check for success
    if ($stmt->execute()) {
        // Set JSON response header
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "Record deleted successfully"]);
    } else {
        // Return error from prepared statement
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Error deleting record: " . $stmt->error]);
    }

    $stmt->close();
} else {
    // Invalid ID provided
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Invalid ID"]);
}

// Close the database connection
$conn->close();
?>
