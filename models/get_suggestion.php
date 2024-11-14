<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory"; // Changed to a valid DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
<?php
// Include your database connection file
require 'db_connection.php'; // Adjust the path as necessary

// Get the type and query from the request
$type = isset($_GET['type']) ? $_GET['type'] : '';
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Initialize an empty array for results
$results = [];

if ($type === 'products') {
    // Prepare the SQL statement based on the type
    $sql = "SELECT product_id AS id, product_name AS text FROM products WHERE product_name LIKE ? LIMIT 10";
    
    // Execute the prepared statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Set the content type to JSON
header('Content-Type: application/json');

// Return results as JSON
echo json_encode($results);
?>
