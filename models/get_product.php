<?php

require_once '../includes/db_connection.php';

// Fetch product details
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT product_id, product_name, barcode, category_name, buying_price, selling_price, stocks, threshold FROM products WHERE product_id=?");
    $stmt->bind_param("i", $product_id);

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Output product details as JSON
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["Error" => "Product not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
