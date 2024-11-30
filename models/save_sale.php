<?php
header('Content-Type: application/json');

require_once '../includes/db_connection.php';

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    error_log('Received data: ' . print_r($data, true)); // Log received data

    // Step 1: Fetch user_id for the given cashier_username
    $stmt = $conn->prepare("SELECT user_id FROM user_management WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('s', $data['cashier_username']);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    // Prepare the SQL statement to insert sale data
    $stmt = $conn->prepare("INSERT INTO sales (sub_total, grand_total, payment, change_amount, transaction_time, cashier_username, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log('Prepare failed: ' . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    // Bind parameters
    if (!$stmt->bind_param('ddddssi', $data['subTotal'], $data['grandTotal'], $data['payment'], $data['change'], $data['transactionTime'], $data['cashier_username'], $user_id)) {
        error_log('Binding parameters failed: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Binding parameters failed: ' . $stmt->error]);
        exit;
    }

    // Execute statement
    if (!$stmt->execute()) {
        error_log('Execute failed: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
        exit;
    }

    $saleId = $stmt->insert_id;
    $stmt->close();

    // Prepare and execute the insertion into the sales_products table without product_name
    $stmt = $conn->prepare("INSERT INTO sales_products (sale_id, product_id, quantity, amount) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log('Prepare for product insert failed: ' . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare for product insert failed: ' . $conn->error]);
        exit;
    }

    foreach ($data['products'] as $product) {
        // Note that 'product_name' has been removed from this line
        $stmt->bind_param('iidd', $saleId, $product['productId'], $product['quantity'], $product['amount']);
        
        if (!$stmt->execute()) {
            error_log('Product insert failed: ' . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Product insert failed: ' . $stmt->error]);
            exit;
        }

        // Update product stock after inserting the sale
        if (!updateProductStock($product['productId'], $product['quantity'], $conn)) {
            error_log("Failed to update stock for product ID: " . $product['productId']);
        }
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

// Function to update the product stock
function updateProductStock($product_id, $quantity_sold, $conn) {
    // Fetch the current stock
    $sql = "SELECT stocks FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($current_stock);
        $stmt->fetch();
        $stmt->close();

        // Check if the product exists
        if ($current_stock !== null) {
            $new_stock = $current_stock - $quantity_sold;

            // Ensure the stock doesn't go negative
            if ($new_stock < 0) {
                $new_stock = 0;
            }

            // Update the stock in the database
            $sql = "UPDATE products SET stocks = ? WHERE product_id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("ii", $new_stock, $product_id);
                $stmt->execute();
                $stmt->close();
                return true; // Indicate success
            } else {
                error_log("Failed to prepare statement for stocks update: " . $conn->error);
                return false; // Indicate failure
            }
        } else {
            error_log("Product not found for ID: " . $product_id);
            return false; // Indicate failure
        }
    } else {
        error_log("Failed to prepare statement for fetching stocks: " . $conn->error);
        return false; // Indicate failure
    }
}
?>
