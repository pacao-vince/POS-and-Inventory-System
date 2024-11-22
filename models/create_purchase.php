<?php
header('Content-Type: application/json');

require_once '../includes/db_connection.php';

// Process form submission for adding a new purchase
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_purchase'])) {
    $product_id = $_POST['product_id'];
    $supplier_id = $_POST['supplier_id'];
    $date = $_POST['date'];
    $purchase_quantity = $_POST['purchase_quantity'];
    $purchase_amount = $_POST['purchase_amount'];

    // // Handle new product
    // if (is_numeric($product_id)) {
    //     // Existing product
    //     $product_id = intval($product_id);
    // } else {
    //     // New product
    //     $product_name = $_POST['new_product']; // From hidden input
    //     $stmt = $conn->prepare("INSERT INTO products (product_name) VALUES (?)");
    //     $stmt->bind_param('s', $product_name);
    //     $stmt->execute();
    //     $product_id = $stmt->insert_id; // Get the inserted product ID
    // }

    try {
        // Prepare and bind for adding purchase
        $stmt = $conn->prepare("INSERT INTO purchases (product_id, supplier_id, date, purchase_quantity, purchase_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisid", $product_id, $supplier_id, $date, $purchase_quantity, $purchase_amount);

        // Execute the query
        if ($stmt->execute()) {
            // Update the stock of the product
            $updateStockQuery = "UPDATE products SET stocks = stocks + ? WHERE product_id = ?";
            $stockStmt = $conn->prepare($updateStockQuery);
            $stockStmt->bind_param("ii", $purchase_quantity, $product_id);
            $stockStmt->execute();
                // If the category was successfully added
            echo json_encode(['success' => true, 'message' => 'Purchase added successfully.']);
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

