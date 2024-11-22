<?php
require_once '../includes/auth.php';

// Only allow Admin access
if ($_SESSION['user_type'] !== 'cashier') {
    logout(); // Call logout to clear the session and redirect
}

// Retrieve the session username for display
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Cashier';

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    
    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pos&inventory";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        error_log('Connection failed: ' . $conn->connect_error);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }

    // Prepare SQL statement
    $sql = "SELECT product_id, product_name, selling_price FROM products WHERE barcode = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log('Prepare failed: ' . $conn->error);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit();
    }
    
    // Bind and execute
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch result and return as JSON
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        error_log('Product not found for barcode: ' . $barcode);
        header('Content-Type: application/json');
        echo json_encode([]);  // Empty array indicates product not found
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/cashier.css">
    

</head>
<body>
    <div class="custom-container">
        <div class="header">
            <h1>POS SYSTEM</h1>
            <div class="cashier-profile" id="cashierProfile">
                <img src="../assets/images/account-avatar-profile-user-14-svgrepo-com.png" alt="Cashier" id="profilePic">
                <span>Cashier, <?php echo htmlspecialchars($user_name); ?></span>
                <div class="profile-dropdown" id="profileDropdown">
                <a id="logout" href="#"><i class="fas fa-sign-out-alt me-2"></i> Log out</a>
                 </div>
            </div>

        </div>

        <div class="main-content">  
            <div class="left-panel">
                <div class="search-section">
                    <form id="searchForm">
                        <input type="text" id="searchInput" name="search" placeholder="Search Product by Name or Barcode">
                        <button type="submit">Search</button>
                    </form>
                </div>
            
                <!-- Hidden barcode input field -->
                <input type="text" id="barcodeInput" style="position: absolute; left: -9999px;" autofocus>
                
                <div class="table-section">
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th>Item No.</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="productListBody">
                            <!-- Dynamic content will be inserted here -->
                        </tbody>
                    </table>
                </div>

                <div id="searchResultsPopup" class="popup">
                    <table id="searchResultsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Selling Price</th>
                            </tr>
                        </thead>
                        <tbody id="searchResultsBody">
                            <!-- Dynamic search results will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="divider"></div>

            <div class="right-panel">
                <div class="totals-section">
                    <div class="sub-total">
                        <span>Sub Total:</span>
                        <span id="subTotal">₱0.00</span>
                    </div>
                    <div class="grand-total">
                        <span>Grand Total:</span>
                        <span id="grandTotal">₱0.00</span>
                    </div>

                    <div class="input-group">
                        <label for="payment">Payment:</label>
                        <input type="number" id="payment" name="payment" required min="0" step="0.01" requred>
                    </div>
                    <div class="change">
                        <span>Change:</span>
                        <span id="changeAmount">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="date-time">
                    <!-- Real-time date and time will be displayed here -->
        </div>
            

        <div class="actions-section">
            <button class="btn btn-primary updatebtn" accesskey="u"><i class="fas fa-boxes me-2"></i> QTY</button>
            <button class="btn btn-danger deletebtn" accesskey="d"><i class="fas fa-trash me-2"></i> DELETE</button>
            <button class="btn btn-success savebtn" accesskey="s"><i class="fas fa-save me-2"></i> SAVE SALE</button>
        </div>
    </div>


<!-- Quantity Modal -->
<div class="modal fade" id="qtyModal" tabindex="-1" role="dialog" aria-labelledby="qtyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document"> 
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 320px;"> 
            <div class="modal-header border-0" style="padding: 0 10px;"> 
                <h5 class="modal-title w-100 text-center text-primary font-weight-bold" id="qtyModalLabel">Update Quantity</h5>
            </div>
            <div class="modal-body text-left" style="padding: 10px 10px;"> 
                <label for="newQuantityInput" class="font-weight-bold">New Quantity:</label>
                <input type="number" id="newQuantityInput" class="form-control mx-auto" min="1" placeholder="1" required>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;"> 
                <button type="button" id="updateQtyBtn" class="btn btn-primary" style="padding:6px 12px;">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="padding:6px 12px;">Cancel</button>
            </div>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 350px;"> 
            <div class="modal-header border-0" style="padding: 0 10px;"> 
                <h5 class="modal-title w-100 text-center text-primary font-weight-bold" id="deleteModalLabel">Delete Item</h5>
            </div>
            <div class="modal-body text-center" style="padding: 10px 10px;"> 
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;"> 
                <button type="button" id="confirmDeleteBtn" class="btn btn-primary w-25">Delete</button> 
                <button type="button" class="btn btn-secondary w-25" data-dismiss="modal">Cancel</button> 
            </div>
        </div>
    </div>
</div>


  <!-- Save Sale Confirmation Modal -->
<div class="modal fade" id="saveSaleConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="saveSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 350px;"> <
            <div class="modal-header border-0" style="padding: 0 10px;"> 
                <h5 class="modal-title w-100 text-center text-primary font-weight-bold" id="saveSaleModalLabel">Save Transaction</h5>
            </div>
            <div class="modal-body text-center" style="padding: 10px 10px;"> 
                <p>Are you sure you want to save this sale?</p>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;"> 
                <button type="button" id="confirmSaveSaleBtn" class="btn btn-primary w-25">Save</button> 
                <button type="button" class="btn btn-secondary w-25" data-dismiss="modal">Cancel</button> 
            </div>
        </div>
    </div>
</div>

<!-- Admin Authentication Modal -->
<div class="modal fade" id="adminAuthModal" tabindex="-1" aria-labelledby="adminAuthLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminAuthLabel">Admin Authentication</h5>
            </div>
            <div class="modal-body">
               
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" placeholder="Enter Username">
                </div>
        
                <div class="mb-3">
                    <label for="adminPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="adminPassword" placeholder="Enter Password">
                </div>

                <div id="authError" class="text-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="authConfirmBtn" class="btn btn-primary">Confirm</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 350px;">
            <div class="modal-header border-0" style="padding: 0 10px;">
                <h5 class="modal-title w-100 text-center text-danger font-weight-bold" id="logoutModalLabel">Logout</h5>
            </div>
            <div class="modal-body text-center" style="padding: 10px 10px;">
                <p>Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;">
                <button type="button" id="confirmLogoutBtn" class="btn btn-danger w-25">Logout</button>
                <button type="button" class="btn btn-secondary w-25" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../controllers/saveSales.js"></script> 
    <script src="../controllers/cashier.js"></script>
    <script src="../controllers/barcodeScanner.js"></script>

</body>
</html>
