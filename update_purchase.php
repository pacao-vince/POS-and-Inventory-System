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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required POST variables are set
    if (isset($_POST['purchase_id'], $_POST['product_id'], $_POST['supplier'], $_POST['date'], $_POST['purchase_amount'])) {
        $purchase_id = $_POST['purchase_id'];
        $product_id = $_POST['product_id'];
        $supplier = $_POST['supplier'];
        $date = $_POST['date'];
        $purchase_amount = $_POST['purchase_amount'];

        // Prepare an SQL statement for execution
        $stmt = $conn->prepare("UPDATE purchases SET product_id=?, supplier=?, date=?, purchase_amount=? WHERE purchase_id=?");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters (adjust types as needed)
        $stmt->bind_param("isssi", $product_id, $supplier, $date, $purchase_amount, $purchase_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Purchase updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating purchase: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    }
}

// Close the connection
$conn->close();
?>
