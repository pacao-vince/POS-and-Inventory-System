<?php
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

// Fetch product details
if (isset($_GET['sale_id'])) {
    $sale_id = $_GET['sale_id'];

    // Prepare and bind
    $stmt = $conn->prepare("
        SELECT sales.sale_id, sales.transaction_time, sales_products.product_name, 
               sales_products.quantity, sales_products.amount, sales.payment, 
               sales.change_amount, sales.cashier_username
        FROM sales
        JOIN sales_products ON sales.sale_id = sales_products.sale_id
        WHERE sales.sale_id = ?");
    $stmt->bind_param("i", $sale_id);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output product details as JSON
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["Error" => "Sale not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
