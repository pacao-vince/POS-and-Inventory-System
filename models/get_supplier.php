<?php

require_once '../includes/db_connection.php';

// Fetch product details
if (isset($_GET['supplier_id'])) {
    $supplier_id = $_GET['supplier_id'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT supplier_id, name, address, contact_num FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $purchase_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Output product details as JSON
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(["Error" => "Supplier not found"]);
        }
    } else {
        echo json_encode(["Error" => "Query execution failed"]);
    }

    $stmt->close();
} else {
    echo json_encode(["Error" => "Supplier ID is missing"]);
}

$conn->close();
?>
