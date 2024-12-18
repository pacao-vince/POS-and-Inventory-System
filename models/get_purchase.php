<?php
require_once '../includes/db_connection.php';

// Fetch product details
if (isset($_GET['purchase_id'])) {
    $purchase_id = $_GET['purchase_id'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT purchase_id, product_name, supplier, date, purchase_quantity, purchase_amount FROM purchases WHERE purchase_id = ?");
    $stmt->bind_param("i", $purchase_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Output product details as JSON
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(["Error" => "Purchase not found"]);
        }
    } else {
        echo json_encode(["Error" => "Query execution failed"]);
    }

    $stmt->close();
} else {
    echo json_encode(["Error" => "Purchase ID is missing"]);
}

$conn->close();
?>
