<?php
 session_start();
 if (!isset($_SESSION['username'])) {
     // Redirect to login page if not logged in
     header('Location: login.php');
     exit();
 }

 // Only allow Admin access
 if ($_SESSION['user_type'] !== 'admin') {
     header('Location: login.php');
     exit();
 }
?>


<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>POS System Product Management</title>
        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="main.css">
    
    </head>
    <body>

        <?php include 'sidebar.php'; ?>

        <div class="main-content" id="main-content">
            <header>
                <h1></h1>
                <div class="admin-profile">
                    <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                    <span>Administrator</span>
                </div>
            </header>
            <div class="products-content" id="products">
                <section class="product-list">
                    <h1>Product Management</h1>
                    <button class="btn btn-primary custom-btn float-right" data-bs-toggle="modal" data-bs-target="#addModal">Add Product</button>
                    <table>
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Barcode</th>
                                <th>Category</th>
                                <th>Buying Price</th>
                                <th>Selling Price</th>
                                <th>Stocks</th>
                                <th>Threshold</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                               
                                // Database connection
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
                                
                                // Pagination variables
                                $products_per_page = 10; // Number of products per page
                                $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $offset = ($current_page - 1) * $products_per_page;

                                // Fetch total number of products for pagination
                                $total_products_sql = "SELECT COUNT(*) AS total FROM products";
                                $total_result = $conn->query($total_products_sql);
                                $total_row = $total_result->fetch_assoc();
                                $total_products = $total_row['total'];
                                $total_pages = ceil($total_products / $products_per_page);

                                // Fetch categories once and store in a variable
                                $sql = "SELECT * FROM category";
                                $category_result = $conn->query($sql);
                                $categories = [];
                                if ($category_result->num_rows > 0) {
                                    while ($category = $category_result->fetch_assoc()) {
                                        $categories[] = $category;
                                    }
                                } else {
                                    $categories = null; // Handle case where no categories are found
                                }

                               // Process form submission for adding a new product
                                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
                                    $product_name = $_POST['product_name'];
                                    $barcode = $_POST['barcode'];
                                    $category_id = $_POST['category_id'];
                                    $buying_price = $_POST['buying_price'];
                                    $selling_price = $_POST['selling_price'];
                                    $stocks = $_POST['stocks'];
                                    $threshold = $_POST['threshold'];

                                    // Prepared statement to prevent SQL injection
                                    $stmt = $conn->prepare("INSERT INTO products (product_name, barcode, category_id, buying_price, selling_price, stocks, threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                    $stmt->bind_param("ssidiii", $product_name, $barcode, $category_id, $buying_price, $selling_price, $stocks, $threshold);

                                    if ($stmt->execute()) {
                                        // Trigger JavaScript alert for successful addition
                                        echo "<script>
                                            window.onload = function() {
                                                showAlert('Product added successfully!', 'success');
                                            };
                                            setTimeout(function() {
                                                window.location.href = 'products.php'; // Redirect after 3 seconds
                                            }, 3000);
                                        </script>";
                                    } else {
                                        // Show error alert in case of failure
                                        echo "<script>
                                            window.onload = function() {
                                                showAlert('Error: Could not add product.', 'danger');
                                            };
                                        </script>";
                                    }

                                $stmt->close();
                            }

                                // Fetch products from database with limit and offset
                                $sql = "SELECT p.product_id, p.product_name, p.barcode, p.buying_price, p.selling_price, p.stocks, p.threshold, c.category_name
                                FROM products p
                                JOIN category c ON p.category_id = c.category_id
                                ORDER BY p.product_id DESC
                                LIMIT $offset, $products_per_page";
                        
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    // Output data of each row
                                    while ($row = $result->fetch_assoc()) {
                                        $stockClass = ($row["stocks"] <= $row["threshold"]) ? "low-stock" : "high-stock";
                                        echo "<tr data-product-id='" . $row["product_id"] . "'>
                                                <td>" . $row["product_id"] . "</td>
                                                <td>" . $row["product_name"] . "</td>
                                                <td>" . $row["barcode"] . "</td>
                                                <td>" . $row["category_name"] . "</td>
                                                <td>₱" . number_format($row["buying_price"], 2) . "</td>
                                                <td>₱" . number_format($row["selling_price"], 2) . "</td>
                                                <td><span class='$stockClass'>" . $row["stocks"] . "</span></td>
                                                <td>" . number_format($row["threshold"]) . "</td>
                                                <td>
                                                    <button class='btn btn-success editBtn' data-id='" . $row['product_id'] . "'>Edit</button> |
                                                    <button class='btn btn-danger deleteBtn' data-id='" . $row['product_id'] . "'>Delete</button>
                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9'>No products found</td></tr>";
                                }

                            ?>
                        </tbody>
                    </table>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                            <a href="?page=<?php echo $page; ?>"<?php echo $page == $current_page ? ' class="active"' : ''; ?>><?php echo $page; ?></a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Add  Modal -->
            <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="addModalLabel">Add Product</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="products.php" method="POST">
                                <div class="mb-3">
                                    <label for="product_name" class="form-label">Product Name:</label>
                                    <input type="text" class="form-control" name="product_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode:</label>
                                    <input type="text" class="form-control" name="barcode" required>
                                </div>
                               
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category:</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <?php if ($categories): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No category available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="buying_price" class="form-label">Buying Price:</label>
                                    <input type="number" class="form-control" name="buying_price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="selling_price" class="form-label">Selling Price:</label>
                                    <input type="number" class="form-control" name="selling_price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stocks" class="form-label">Stocks:</label>
                                    <input type="number" class="form-control" name="stocks" required>
                                </div>
                                <div class="mb-3">
                                    <label for="threshold" class="form-label">Threshold:</label>
                                    <input type="number" class="form-control" name="threshold" required>
                                </div>
                                <div class="modal-footer">
                                <button type="submit" name="add_product" class="btn btn-primary custom-btn-color">Add Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit  Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="editModalLabel">Edit Product</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="update_product.php" method="POST">
                        <input type="hidden" id="edit_product_product_id" name="product_id">
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label">Product Name:</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_barcode" class="form-label">Barcode:</label>
                            <input type="text" class="form-control" id="edit_barcode" name="barcode" required>
                        </div>

                        <div class="mb-3">
                        <label for="category_id" class="form-label">Category:</label>
                        <select class="form-control" id="edit_category_id" name="category_id" required>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No category available</option>
                            <?php endif; ?>
                        </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_buying_price" class="form-label">Buying Price:</label>
                            <input type="number" class="form-control" id="edit_buying_price" name="buying_price" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_selling_price" class="form-label">Selling Price:</label>
                            <input type="number" class="form-control" id="edit_selling_price" name="selling_price" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_stocks" class="form-label">Stocks:</label>
                            <input type="number" class="form-control" id="edit_stocks" name="stocks" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_threshold" class="form-label">Threshold:</label>
                            <input type="number" class="form-control" id="edit_threshold" name="threshold" required>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary custom-btn-color">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete  Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="deleteModalLabel">Delete Product</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="deleteForm" action="delete_product.php" method="POST">
                        <input type="hidden" id="delete_product_product_id" name="product_id">
                        <p>Are you sure you want to delete this product?</p>
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
            <script src="products.js"></script>
        </div>
    </body>
    </html>
    <?php
    $conn->close();
    ?>