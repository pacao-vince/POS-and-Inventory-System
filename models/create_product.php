<?php
header('Content-Type: application/json');

require_once '../includes/db_connection.php';

// Process form submission for adding a new product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $barcode = $_POST['barcode'];
    $category_id = $_POST['category_id'];
    $buying_price = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $stocks = $_POST['stocks'];
    $threshold = $_POST['threshold'];

    try {
        // Prepare and bind the SQL statement to insert the new category
        $stmt = $conn->prepare("INSERT INTO products (product_name, barcode, category_id, buying_price, selling_price, stocks, threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidiii", $product_name, $barcode, $category_id, $buying_price, $selling_price, $stocks, $threshold);

        // Execute the query
        if ($stmt->execute()) {
            // If the category was successfully added
            echo json_encode(['success' => true, 'message' => 'Product added successfully.']);
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
