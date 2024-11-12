<?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

    if ($_SESSION['user_type'] !== 'admin') {
        header('Location: login.php');
        exit();
    }
    
    include 'sidebar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Purchases Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="main.css">
   
   
</head>
<body>

    <div class="main-content" id="main-content">
        <header>
            <h1>Purchases Management</h1>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>

        <div class="table-content">
            <section class="table-list">
                <button class="btn btn-primary add-purchase-btn custom-btn float-right" data-bs-toggle="modal" data-bs-target="#addModal">Add Purchase</button>
                <table class="Table">
                    <thead>
                        <tr>
                            <th>ID</th>
							<th>Product Name</th>
							<th>Supplier</th>
							<th>Date</th>
                            <th>Quantity</th>
							<th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
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

                        // Pagination variables
                        $purchases_per_page = 10; // Number of products per page
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($current_page - 1) * $purchases_per_page;

                        // Fetch total number of products for pagination
                        $total_purchases_sql = "SELECT COUNT(*) AS total FROM purchases";
                        $total_result = $conn->query($total_purchases_sql);
                        $total_row = $total_result->fetch_assoc();
                        $total_purchases = $total_row['total'];
                        $total_pages = ceil($total_purchases / $purchases_per_page);

                        // Fetch products once and store in a variable
                        $sql = "SELECT * FROM products";
                        $product_result = $conn->query($sql);
                        $products = [];
                        if ($product_result->num_rows > 0) {
                            while ($product = $product_result->fetch_assoc()) {
                                $products[] = $product;
                            }
                        } else {
                            $products = null; // Handle case where no products are found
                        }


                        // Fetch suppliers from the database
                        $sql = "SELECT * FROM suppliers";
                        $supplier_result = $conn->query($sql);
                        $suppliers = [];
                        if ($supplier_result->num_rows > 0) {
                            while ($supplier = $supplier_result->fetch_assoc()) {
                                $suppliers[] = $supplier;
                            }
                        }

                        // Process form submission for adding a new purchase
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_purchase'])) {
                            $product_id = $_POST['product_id'];
                            $supplier_id = $_POST['supplier_id'];
                            $date = $_POST['date'];
                            $purchase_quantity = $_POST['purchase_quantity'];
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
                            $stmt = $conn->prepare("INSERT INTO purchases (product_id, supplier_id, date, purchase_quantity, purchase_amount) VALUES (?, ?, ?, ?, ?)");
                            $stmt->bind_param("iisid", $product_id, $supplier_id, $date, $purchase_quantity, $purchase_amount);

                            if ($stmt->execute()) {
                                // Trigger JavaScript alert for successful addition
                                echo "<script>
                                    window.onload = function() {
                                        showAlert('Purchase added successfully!', 'success');
                                    };
                                    setTimeout(function() {
                                        window.location.href = 'purchases.php'; // Redirect after 3 seconds
                                    }, 3000);
                                </script>";
                            } else {
                                // Show error alert in case of failure
                                echo "<script>
                                    window.onload = function() {
                                        showAlert('Error: Could not add purchase.', 'danger');
                                    };
                                </script>";
                            }
                        
                            $stmt->close();
                        }

                        // Fetch products from database
                        $sql = "SELECT 
                                    purchases.purchase_id,
                                    purchases.product_id,
                                    products.product_name,
                                    purchases.supplier_id,
                                    suppliers.name AS supplier_name, 
                                    purchases.date,
                                    purchase_quantity,
                                    purchases.purchase_amount
                                FROM purchases 
                                JOIN products ON purchases.product_id = products.product_id
                                JOIN suppliers ON suppliers.supplier_id = purchases.supplier_id
                                ORDER BY purchase_id DESC LIMIT $offset, $purchases_per_page";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-purchase-id='" . htmlspecialchars($row['purchase_id']) . "' 
                                    data-product-id='" . htmlspecialchars($row['product_id']) . "' 
                                    data-supplier-id='" . htmlspecialchars($row['supplier_id']) . "'>
                                
                                    <td>" . htmlspecialchars($row["purchase_id"]) . "</td>
                                    <td>" . htmlspecialchars($row["product_name"]) . "</td>
                                    <td>" . htmlspecialchars($row["supplier_name"]) . "</td>
                                    <td>" . htmlspecialchars($row["date"]) . "</td>
                                    <td>" . htmlspecialchars($row["purchase_quantity"]) . "</td>
                                    <td>" . htmlspecialchars($row["purchase_amount"]) . "</td>
                                    <td>
                                        <button class='btn btn-success editBtn font-size' id='editBtn' data-id='" . $row['purchase_id'] . "'> <i class='fas fa-edit me-2'></i>Edit</button> |
                                        <button class='btn btn-danger deleteBtn font-size' id='deleteBtn' data-id='" . $row['purchase_id'] . "'> <i class='fas fa-trash me-2'></i>Delete</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No purchases found.</td></tr>";
                        }

                    ?>
                </tbody>
            </table>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                <?php else: ?>
                    <span class="disabled">Previous</span>
                <?php endif; ?>


                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>"<?php echo $page == $current_page ? ' class="active"' : ''; ?>><?php echo $page; ?></a>
                    <?php endfor; ?>
              
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
                <?php else: ?>
                    <span class="disabled">Next</span>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- Add  Modal -->
<div id="addModal" class="modal fade" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="addModalLabel">Add New Purchase</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            
                <form id="addForm" action="purchases.php" method="POST">
                <div class="mb-3">
                    <label for="product_id" class="form-label">Product:</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="" disabled selected>Select Product</option> <!-- Placeholder option -->
                            <?php if ($products): ?>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo htmlspecialchars($product['product_id']); ?>">
                                        <?php echo htmlspecialchars($product['product_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No product available</option>
                            <?php endif; ?>
                        </select>
                </div>

                   <div class="mb-3">
                        <label for="supplier" class="form-label">Supplier:</label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <?php if ($suppliers): ?>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['supplier_id']); ?>">
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No suppliers available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date:</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="purchase_quantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="purchase_quantity" name="purchase_quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="purchase_amount" class="form-label">Amount:</label>
                        <input type="number" class="form-control" id="purchase_amount" name="purchase_amount" step="0.01" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary custom-btn-color" name="add_purchase">Add Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit  Modal -->
<div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="editModalLabel">Edit Purchase</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="update_purchase.php" method="POST">
                    <input type="hidden" id="update_purchase_id" name="purchase_id">
                    <div class="mb-3">

                    <label for="product" class="form-label">Product:</label>
                        <select class="form-select" id="edit_product_id" name="product_id" required>
                            <?php if ($products): ?>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo htmlspecialchars($product['product_id']); ?>">
                                        <?php echo htmlspecialchars($product['product_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No product available</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="supplier" class="form-label">Supplier:</label>
                        
                        <select class="form-select" id="edit_supplier_id" name="supplier_id" required>
                            <?php if ($suppliers): ?>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['supplier_id']); ?>">
                                        <?php echo htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No suppliers available</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Date:</label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_purchase_quantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="edit_purchase_quantity" name="purchase_quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_purchase_amount" class="form-label">Amount:</label>
                        <input type="number" class="form-control" id="edit_purchase_amount" name="purchase_amount" step="0.01" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary custom-btn-color">Update Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete  Modal -->
<div id="deleteModal" class="modal fade" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="deleteModalLabel">Delete Purchase</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deleteForm" action="delete_purchase.php" method="POST"> 
                    <input type="hidden" id="delete_purchase_id" name="purchase_id"> 
                    <p>Are you sure you want to delete this purchase?</p>
                    <div class="modal-footer">
                        <button type="submit" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="purchases.js"></script>
</body>
</html>
<?php $conn->close();?>