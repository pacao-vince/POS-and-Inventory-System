<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to avoid character set issues
$conn->set_charset("utf8");

$query = isset($_GET['q']) ? $_GET['q'] : '';
$results = [];

if (!empty($query)) {
    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT product_id, product_name, selling_price FROM products WHERE product_name LIKE ? OR barcode LIKE ?");
    if ($stmt === false) {
        error_log('Prepare failed: ' . $conn->error);
        echo json_encode([]);
        exit;
    }
    
    $searchQuery = "%" . $query . "%";
    $stmt->bind_param("ss", $searchQuery, $searchQuery);
    
    if (!$stmt->execute()) {
        error_log('Execute failed: ' . $stmt->error);
        echo json_encode([]);
        exit;
    }
    
    $result = $stmt->get_result();
    if ($result === false) {
        error_log('Get result failed: ' . $stmt->error);
        echo json_encode([]);
        exit;
    }

    // Fetch the results
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            error_log(print_r($row, true)); // Log each row to debug
            $results[] = $row;
        }
    } else {
        error_log('No matching records found.');
    }

    $stmt->close();
} else {
    error_log('Empty search query');
}

$conn->close();

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($results);
?>
