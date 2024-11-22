<?php
// Ensure no output before setting headers
header('Content-Type: application/json');

// Enable error reporting for debugging (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection here
require_once '../includes/db_connection.php'; 

// Check if the form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    // Get and sanitize the category name
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

    // Validate input
    if (empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'Category name cannot be empty.']);
        exit;
    }

    try {
        // Prepare and bind the SQL statement to insert the new category
        $stmt = $conn->prepare("INSERT INTO category (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);

        // Execute the query
        if ($stmt->execute()) {
            // If the category was successfully added
            echo json_encode(['success' => true, 'message' => 'Category added successfully.']);
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
} else {
    // If it's not a POST request, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

exit;
?>
