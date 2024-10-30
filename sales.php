<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Sales Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="main.css">
    
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="main-content">
        <header>
            <h2></h2>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
        <div class="table-content" id="products">
            <section class="table-list">
                <h1>Sales Management</h1>
                
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
                            <th>Cashier</th>
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
                        $sales_per_page = 10; // Number of sales per page
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($current_page - 1) * $sales_per_page;

                        // Fetch total number of sales for pagination
                        $total_sales_sql = "SELECT COUNT(*) AS total FROM sales";
                        $total_result = $conn->query($total_sales_sql);
                        $total_row = $total_result->fetch_assoc();
                        $total_sales = $total_row['total'];
                        $total_pages = ceil($total_sales / $sales_per_page);

                        // Fetch sales with limit and offset
                        $sql = "SELECT 
                                    sales.sale_id,
                                    sales.transaction_time,
                                    products.product_name,
                                    sales_products.quantity,
                                    sales_products.amount,
                                    sales.payment,
                                    sales.change_amount,
                                    sales.cashier_username
                                FROM sales
                                JOIN sales_products ON sales.sale_id = sales_products.sale_id
                                JOIN products ON sales_products.product_id = products.product_id
                                ORDER BY sales.sale_id DESC 
                                LIMIT $offset, $sales_per_page";
                        
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

                                echo "<tr>
                                        <td>" . $row["sale_id"] . "</td>
                                        <td>" . $transactionTime12Hour . "</td>
                                        <td>" . $row["product_name"] . "</td>
                                        <td>" . $row["quantity"] . "</td>
                                        <td>₱ " . $row["amount"] . "</td>
                                        <td>₱ " . $row["payment"] . "</td>
                                        <td>₱ " . $row["change_amount"] . "</td>
                                        <td>" . $row["cashier_username"] . "</td>
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
                        <a href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>"<?php echo $page == $current_page ? ' class="active"' : ''; ?>>
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script src="sales.js"></script>
</body>
</html>
