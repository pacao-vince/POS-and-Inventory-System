<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get product ID from the request
    $product_id = isset($_POST['product_id']) ? $conn->real_escape_string($_POST['product_id']) : null;

    if ($product_id) {
        // Prepare SQL statement to delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $product_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    }
}

// Close the database connection
$conn->close();
?>
