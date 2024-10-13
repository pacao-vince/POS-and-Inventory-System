<?php
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch aggregated bestseller data
$query = "SELECT product_name, SUM(amount) AS total_amount 
          FROM sales_products 
          GROUP BY product_name 
          ORDER BY total_amount DESC
           LIMIT 10";
$result = $conn->query($query);

// Initialize arrays to hold the data
$product_name = [];
$total_amount = [];

// Fetch the data
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $product_name[] = $row['product_name'];
        $total_amount[] = $row['total_amount'];
    }
} else {
    echo json_encode(["error" => $conn->error]);
    exit();
}

// Close connection
$conn->close();

// Return data in JSON format
echo json_encode([
    'product_name' => $product_name,
    'total_amount' => $total_amount
]);
?>
