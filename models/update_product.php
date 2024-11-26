<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db_connection.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $product_id = $conn->real_escape_string($_POST['product_id']);
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $barcode = $conn->real_escape_string($_POST['barcode']);
    $category_id = $conn->real_escape_string($_POST['category_id']);
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $stocks = $_POST['stocks'];
    $threshold = $_POST['threshold'];

    // Prepare an SQL statement for execution
    $stmt = $conn->prepare("UPDATE products SET product_name=?, barcode=?, category_id=?, buying_price=?, selling_price=?, stocks=?, threshold=? WHERE product_id=?");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssiddiis", $product_name, $barcode, $category_id, $buying_price, $selling_price, $stocks, $threshold, $product_id);

   
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $stmt->error]);
    }
    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
