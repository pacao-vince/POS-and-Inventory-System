<?php
header('Content-Type: application/json');

// Database connection parameters
require_once '../includes/db_connection.php';

// Query to fetch aggregated bestseller data
$query = "SELECT p.product_name, SUM(sp.amount) AS total_amount 
          FROM sales_products sp
          JOIN products p ON sp.product_id = p.product_id
          GROUP BY p.product_name 
          ORDER BY total_amount DESC
          LIMIT 10;";

$result = $conn->query($query);

// Initialize arrays to hold the data
$product_name = [];
$total_amount = [];

// Check if the query was successful
if ($result) {
    // Fetch the data
    while ($row = $result->fetch_assoc()) {
        $product_name[] = $row['product_name'];
        $total_amount[] = $row['total_amount'];
    }
    
    // Check if we have results
    if (empty($product_name)) {
        echo json_encode(["message" => "No best sellers found."]);
    } else {
        // Return data in JSON format
        echo json_encode([
            'product_name' => $product_name,
            'total_amount' => $total_amount
        ]);
    }
} else {
    // Handle SQL error
    echo json_encode(["error" => "Query error: " . $conn->error]);
}

// Close connection
$conn->close();
?>
