<?php 
require_once '../includes/auth.php';

// Only allow Admin access
if ($_SESSION['user_type'] !== 'admin') {
    logout(); // Call logout to clear the session and redirect
}

include "../includes/db_connection.php";
include '../includes/sidebar.php'; 

// Fetch distinct cashier usernames for the dropdown
$cashierQuery = "SELECT DISTINCT username FROM sales
                JOIN user_management ON sales.user_id = user_management.user_id";
$cashierResult = $conn->query($cashierQuery);

// Check for query errors
if (!$cashierResult) {
    die("Query failed: " . $conn->error);
}

// Pagination variables
$sales_per_page = 10; // Number of sales per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $sales_per_page;

// Filter variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$cashier = isset($_GET['cashier']) ? $_GET['cashier'] : 'All';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Sales Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        @media screen and (max-width: 768px) {
            #filters {
                display: grid; /* Use grid layout */
                grid-template-columns: 1fr 1fr 1fr; /* Two columns: first column for search and second for other filters */
                gap: 10px; /* Spacing between filters */
            }

            /* Ensure the first filter (search input) occupies the full width of the row */
            #filters .col-4 {
                grid-column: span 3; /* Take full width in the first row */
                width: 100%;
            }

            /* Allow the other filters to stay in one grid column */
            #filters .col-2 {
                grid-column: span 1;
                width: 100% !important; /* Ensure they take full width */
                max-width: none; /* Remove any max-width restrictions */
                flex-shrink: 0; /* Prevent shrinking */
            }

            .col-form-label{
                padding-left: 0;
            }
            
        }
    </style>
</head>

<body>
    <div class="main-content" id="main-content">
        <header>
            <h1>Sales Management</h1>
            <?php include '../views/settings_dropdown.php'; ?>
        </header>
        
        <div class="table-content" id="products">
            <section class="table-list">
                <div class="form-row mb-4" id="filters">
                    <div class="col-4">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search Product Name..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-2 d-flex align-items-center ml-auto mr-5">
                        <label for="startDate" class="col-auto col-form-label pr-2">From:</label>
                        <input type="date" class="form-control" id="startDate" name="startDate"
                            value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>

                    <div class="col-2 d-flex align-items-center mr-3">
                        <label for="endDate" class="col-auto col-form-label pr-2">To:</label>
                        <input type="date" class="form-control" id="endDate" name="endDate"
                            value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>

                    <div class="col-2 d-flex align-items-center">
                        <label for="cashierDropdown" class="col-auto col-form-label pr-2">Cashier:</label>
                        <select class="form-select w-100" id="cashierDropdown" name="cashierDropdown">
                            <option value="All" <?php echo $cashier === 'All' ? 'selected' : ''; ?>>All</option>
                            <?php while ($cashierRow = $cashierResult->fetch_assoc()) { ?>
                                <option value="<?php echo htmlspecialchars($cashierRow['username']); ?>"
                                    <?php echo $cashier === $cashierRow['username'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cashierRow['username']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                
                <table class="Table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Transaction Time</th>
                            <th>Product Name</th>
                            <th>Quantity Sold</th>                            
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Change</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php

                    // Fetch sales with limit and offset
                    // $sql = "SELECT 
                    //             sales.sale_id,
                    //             sales.transaction_time,
                    //             products.product_name,
                    //             sales_products.quantity,
                    //             sales_products.amount,
                    //             sales.payment,
                    //             sales.change_amount,
                    //             user_management.username 
                    //         FROM sales
                    //         JOIN sales_products ON sales.sale_id = sales_products.sale_id
                    //         JOIN products ON sales_products.product_id = products.product_id
                    //         LEFT JOIN user_management ON sales.user_id = user_management.user_id
                    //         ORDER BY sales.sale_id DESC 
                    //         LIMIT $offset, $sales_per_page";
                    
                    // Construct the SQL query dynamically
                    $sql = "SELECT 
                    sales.sale_id,
                    sales.transaction_time,
                    products.product_name,
                    sales_products.quantity,
                    sales_products.amount,
                    sales.payment,
                    sales.change_amount,
                    user_management.username 
                    FROM sales
                    JOIN sales_products ON sales.sale_id = sales_products.sale_id
                    JOIN products ON sales_products.product_id = products.product_id
                    LEFT JOIN user_management ON sales.user_id = user_management.user_id
                    WHERE 1=1";

                    // Apply filters
                    if (!empty($search)) {
                    $sql .= " AND products.product_name LIKE '%" . $conn->real_escape_string($search) . "%'";
                    }
                    if (!empty($startDate)) {
                    $sql .= " AND DATE(sales.transaction_time) >= '" . $conn->real_escape_string($startDate) . "'";
                    }
                    if (!empty($endDate)) {
                    $sql .= " AND DATE(sales.transaction_time) <= '" . $conn->real_escape_string($endDate) . "'";
                    }
                    if ($cashier !== 'All') {
                    $sql .= " AND user_management.username = '" . $conn->real_escape_string($cashier) . "'";
                    }

                    // Add ordering and pagination
                    $sql .= " ORDER BY sales.sale_id DESC LIMIT $offset, $sales_per_page";

                    // Fetch filtered results
                    $result = $conn->query($sql);
                    if (!$result) {
                    die("Query failed: " . $conn->error);
                    }

                    // Fetch total rows for pagination
                    $total_sql = "SELECT COUNT(*) AS total FROM sales
                    JOIN sales_products ON sales.sale_id = sales_products.sale_id
                    JOIN products ON sales_products.product_id = products.product_id
                    LEFT JOIN user_management ON sales.user_id = user_management.user_id
                    WHERE 1=1";

                    // Apply the same filters for total count
                    if (!empty($search)) {
                    $total_sql .= " AND products.product_name LIKE '%" . $conn->real_escape_string($search) . "%'";
                    }
                    if (!empty($startDate)) {
                    $total_sql .= " AND DATE(sales.transaction_time) >= '" . $conn->real_escape_string($startDate) . "'";
                    }
                    if (!empty($endDate)) {
                    $total_sql .= " AND DATE(sales.transaction_time) <= '" . $conn->real_escape_string($endDate) . "'";
                    }
                    if ($cashier !== 'All') {
                    $total_sql .= " AND user_management.username = '" . $conn->real_escape_string($cashier) . "'";
                    }

                    $total_result = $conn->query($total_sql);
                    $total_row = $total_result->fetch_assoc();
                    $total_sales = $total_row['total'];
                    $total_pages = ceil($total_sales / $sales_per_page);

                    $result = $conn->query($sql);

                    // Check for query errors 
                    if (!$result) {
                        die("Query failed: " . $conn->error);
                    }

                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while ($row = $result->fetch_assoc()) {
                            // Convert 24-hour format to 12-hour format
                            $transactionTime24Hour = $row['transaction_time']; // e.g., '2024-08-27 21:33:00'
                            $dateTimeObj = new DateTime($transactionTime24Hour);
                            $transactionTime12Hour = $dateTimeObj->format('m/d/Y h:i A'); // 12-hour format

                            echo 
                                "<tr>
                                    <td>" . $row["sale_id"] . "</td>
                                    <td class='sale-date'>" . $transactionTime12Hour . "</td>
                                    <td class='product-name'>" . $row["product_name"] . "</td>
                                    <td>" . $row["quantity"] . "</td>
                                    <td>₱ " . $row["amount"] . "</td>
                                    <td>₱ " . $row["payment"] . "</td>
                                    <td>₱ " . $row["change_amount"] . "</td>
                                    <td class='cashier'>" . $row["username"] . "</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No sales found.</td></tr>";
                    }

                    $conn->close();
                    
                    ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&startDate=<?php echo urlencode($startDate); ?>&endDate=<?php echo urlencode($endDate); ?>&cashier=<?php echo urlencode($cashier); ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>&startDate=<?php echo urlencode($startDate); ?>&endDate=<?php echo urlencode($endDate); ?>&cashier=<?php echo urlencode($cashier); ?>"
                        <?php echo $page == $current_page ? 'class="active"' : ''; ?>>
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&startDate=<?php echo urlencode($startDate); ?>&endDate=<?php echo urlencode($endDate); ?>&cashier=<?php echo urlencode($cashier); ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>

            </section>
        </div>
    </div>
    <script src="../controllers/sales.js"></script>
</body>
</html>
