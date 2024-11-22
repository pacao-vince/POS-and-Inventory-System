<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['purchase_id'])) {
    $purchase_id = $_POST['purchase_id'];

    // Fetch purchase details before deletion
    $fetch_purchase_stmt = $conn->prepare("SELECT product_id, purchase_quantity FROM purchases WHERE purchase_id = ?");
    if ($fetch_purchase_stmt) {
        $fetch_purchase_stmt->bind_param("i", $purchase_id);
        $fetch_purchase_stmt->execute();
        $fetch_purchase_stmt->bind_result($product_id, $purchase_quantity);
        $fetch_purchase_stmt->fetch();
        $fetch_purchase_stmt->close();

        if ($product_id !== null) {
            // Reduce the stock of the product
            $update_stock_stmt = $conn->prepare("UPDATE products SET stocks = stocks - ? WHERE product_id = ?");
            if ($update_stock_stmt) {
                $update_stock_stmt->bind_param("ii", $purchase_quantity, $product_id);
                $update_stock_stmt->execute();
                $update_stock_stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating stock: ' . $conn->error]);
                $conn->close();
                exit;
            }

            // Prepare delete statement
            $delete_stmt = $conn->prepare("DELETE FROM purchases WHERE purchase_id = ?");
            if ($delete_stmt) {
                $delete_stmt->bind_param("i", $purchase_id);
                if ($delete_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Purchase deleted successfully and stock adjusted.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error deleting purchase: ' . $delete_stmt->error]);
                }
                $delete_stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Purchase not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error fetching purchase details: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
