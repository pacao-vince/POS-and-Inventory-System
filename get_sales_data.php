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

// Query to fetch daily sales data
$query = "SELECT 
              DATE_FORMAT(transaction_time, '%d-%m-%Y') AS transaction_time, 
              SUM(grand_total) AS grand_total
			  FROM sales
			  GROUP BY DATE_FORMAT(transaction_time, '%d-%m-%Y')
			  ORDER BY transaction_time";
$result = $conn->query($query);

// Initialize arrays to hold the data
$transaction_time = [];
$grand_total = [];

// Fetch the data
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transaction_time[] = $row['transaction_time'];
        $grand_total[] = $row['grand_total'];
    }
} else {
    echo json_encode(["error" => $conn->error]);
    exit();
}

// Close connection
$conn->close();

// Return data in JSON format
echo json_encode([
    'transaction_time' => $transaction_time,
    'grand_total' => $grand_total
]);
?>
