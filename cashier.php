<?php
include('auth.php');
/*
// Check if the logged-in user is a Cashier
if ($_SESSION['user_type'] !== 'cashier') {
    header('Location: login.php');
    exit();
}

// Retrieve the session username for display
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Cashier';
*/
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="cashier.css">
    

</head>
<body>
    <div class="custom-container">
        <div class="header">
            <h1>POS SYSTEM</h1>
            <div class="cashier-profile" id="cashierProfile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Cashier" id="profilePic">
                <span>Cashier, <?php echo htmlspecialchars($user_name); ?></span>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="login.php" id="logout">Log out</a>
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
            <button class="btn btn-primary updatebtn">QTY</button>
            <button class="btn btn-danger deletebtn">DELETE</button>
            <button class="btn btn-success savebtn">Save Sale</button>
        </div>
    </div>


<!-- Quantity Modal -->
<div class="modal fade" id="qtyModal" tabindex="-1" role="dialog" aria-labelledby="qtyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document"> <!-- Centered modal -->
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 320px;"> <!-- Adjusted padding and width -->
            <div class="modal-header border-0" style="padding: 0 10px;"> <!-- Reduced horizontal padding -->
                <h5 class="modal-title w-100 text-center text-primary font-weight-bold" id="qtyModalLabel">Update Quantity</h5>
            </div>
            <div class="modal-body text-left" style="padding: 10px 10px;"> <!-- Reduced padding for compact look -->
                <label for="newQuantityInput" class="font-weight-bold">New Quantity:</label>
                <input type="number" id="newQuantityInput" class="form-control mx-auto" min="1" placeholder="1" required>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;"> <!-- Footer padding adjustment -->
                <button type="button" id="updateQtyBtn" class="btn btn-primary" style="padding:6px 12px;">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="padding:6px 12px;">Cancel</button>
            </div>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 350px;"> <!-- Adjust width and padding -->
            <div class="modal-header border-0" style="padding: 0 10px;"> <!-- Adjust header padding -->
                <h5 class="modal-title w-100 text-center text-primary font-weight-bold" id="deleteModalLabel">Delete Item</h5>
            </div>
            <div class="modal-body text-center" style="padding: 10px 10px;"> <!-- Adjust body padding -->
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;"> <!-- Footer padding and centering -->
                <button type="button" id="confirmDeleteBtn" class="btn btn-primary w-25">Delete</button> <!-- Blue delete button -->
                <button type="button" class="btn btn-secondary w-25" data-dismiss="modal">Cancel</button> <!-- Gray cancel button -->
            </div>
        </div>
    </div>
</div>


  <!-- Save Sale Confirmation Modal -->
<div class="modal fade" id="saveSaleConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="saveSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 350px;"> <!-- Adjust width and padding -->
            <div class="modal-header border-0" style="padding: 0 10px;"> <!-- Adjust header padding -->
                <h5 class="modal-title w-100 text-center text-primary font-weight-bold" id="saveSaleModalLabel">Save Transaction</h5>
            </div>
            <div class="modal-body text-center" style="padding: 10px 10px;"> <!-- Adjust body padding -->
                <p>Are you sure you want to save this sale?</p>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;"> <!-- Footer padding and centering -->
                <button type="button" id="confirmSaveSaleBtn" class="btn btn-primary w-25">Save</button> <!-- Blue save button -->
                <button type="button" class="btn btn-secondary w-25" data-dismiss="modal">Cancel</button> <!-- Gray cancel button -->
            </div>
        </div>
    </div>
</div>



    <!-- Ensure the JavaScript files are correctly linked -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="saveSales.js"></script> 
    <script src="cashier.js"></script>
    <script src="barcodeScanner.js"></script>

</body>
</html>
