
<?php
include_once 'db_connection.php';

// Process form submission for adding a new purchase
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_purchase'])) {
                        $product_id = $_POST['product_id'];
                        $supplier = $_POST['supplier'];
                        $date = $_POST['date'];
                        $purchase_amount = $_POST['purchase_amount'];

                        // Handle new product
                        if (is_numeric($product_id)) {
                            // Existing product
                            $product_id = intval($product_id);
                        } else {
                            // New product
                            $product_name = $_POST['new_product']; // From hidden input
                            $stmt = $conn->prepare("INSERT INTO products (product_name) VALUES (?)");
                            $stmt->bind_param('s', $product_name);
                            $stmt->execute();
                            $product_id = $stmt->insert_id; // Get the inserted product ID
                        }
                        // Prepare and bind
                        $stmt = $conn->prepare("INSERT INTO purchases (product_id, supplier, date, purchase_amount) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("issd", $product_id, $supplier, $date, $purchase_amount);

                        if ($stmt->execute()) {
                            echo "<script>window.location.href = 'purchases.php';</script>";
                        } else {
                            echo "Error: " . $stmt->error;
                        }

                        $stmt->close();
                    }
?>
