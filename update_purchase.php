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
    // Ensure all necessary fields are set
    if (isset($_POST['purchase_id'], $_POST['product_id'], $_POST['supplier_id'], $_POST['date'], $_POST['purchase_quantity'], $_POST['purchase_amount'])) {
        // Sanitize inputs
        $purchase_id = $_POST['purchase_id'];
        $product_id = $_POST['product_id'];
        $supplier_id = $_POST['supplier_id'];
        $date = $_POST['date'];
        $purchase_quantity = $_POST['purchase_quantity'];
        $purchase_amount = $_POST['purchase_amount'];

        // Prepare and execute the update statement
        $stmt = $conn->prepare("UPDATE purchases SET product_id=?, supplier_id=?, date=?, purchase_quantity=?, purchase_amount=? WHERE purchase_id=?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind the parameters
        $stmt->bind_param("iisidi", $product_id, $supplier_id, $date, $purchase_quantity, $purchase_amount, $purchase_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Purchase updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating purchase: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    }
}

$conn->close();
?>
