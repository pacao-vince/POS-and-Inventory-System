<?php
header('Content-Type: application/json');

require_once '../includes/db_connection.php';

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

        // Get the old purchase quantity for the given purchase_id
        $old_quantity_query = $conn->prepare("SELECT purchase_quantity, product_id FROM purchases WHERE purchase_id = ?");
        if (!$old_quantity_query) {
            die("Prepare failed: " . $conn->error);
        }
        $old_quantity_query->bind_param("i", $purchase_id);
        $old_quantity_query->execute();
        $old_quantity_query->bind_result($old_quantity, $old_product_id);
        $old_quantity_query->fetch();
        $old_quantity_query->close();

        if ($old_quantity === null) {
            echo json_encode(['success' => false, 'message' => 'Purchase not found.']);
            $conn->close();
            exit;
        }

        // Update the purchases table
        $stmt = $conn->prepare("UPDATE purchases SET product_id=?, supplier_id=?, date=?, purchase_quantity=?, purchase_amount=? WHERE purchase_id=?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iisidi", $product_id, $supplier_id, $date, $purchase_quantity, $purchase_amount, $purchase_id);

        if ($stmt->execute()) {
            // Update stocks in the products table
            $quantity_difference = $purchase_quantity - $old_quantity;

            // If the product_id has changed, adjust stocks for both old and new products
            if ($product_id != $old_product_id) {
                // Decrease stock of the old product
                $update_old_stock = $conn->prepare("UPDATE products SET stocks = stocks - ? WHERE product_id = ?");
                $update_old_stock->bind_param("ii", $old_quantity, $old_product_id);
                $update_old_stock->execute();
                $update_old_stock->close();

                // Increase stock of the new product
                $update_new_stock = $conn->prepare("UPDATE products SET stocks = stocks + ? WHERE product_id = ?");
                $update_new_stock->bind_param("ii", $purchase_quantity, $product_id);
                $update_new_stock->execute();
                $update_new_stock->close();
            } else {
                // Adjust stock for the same product
                $update_stock = $conn->prepare("UPDATE products SET stocks = stocks + ? WHERE product_id = ?");
                $update_stock->bind_param("ii", $quantity_difference, $product_id);
                $update_stock->execute();
                $update_stock->close();
            }

            echo json_encode(['success' => true, 'message' => 'Purchase updated and stock adjusted successfully.']);
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
