<?php 
require_once '../includes/auth.php';

// Only allow Admin access
if ($_SESSION['user_type'] !== 'admin') {
    logout(); // Call logout to clear the session and redirect
}

include '../includes/sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Inventory</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <script>
        function printTable() {
            // Hide elements that shouldn't be printed
            var printButton = document.getElementById('printBtn');
            var sidebar = document.getElementById('sidebar');
            var header = document.querySelector('header');

            printButton.style.display = 'none';
            sidebar.style.display = 'none';
            header.style.display = 'none';

            // Print the content
            window.print();

            // Restore the visibility of hidden elements after printing
            printButton.style.display = 'block';
            sidebar.style.display = 'flex';
            header.style.display = 'flex';
        }
    </script>
    <style>
        /* Print Button Style */
        #printBtn {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            padding: 8px 12px;
            font-size: 1.4rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 4px;
            margin-bottom: 16px;
            float: right;
        }

        #printBtn:hover {
            background-color: #218838;
        }
        @media print {
        /* Hide everything except the table and its contents */
        body * {
            visibility: hidden;
        }

        #tableToPrint, #tableToPrint * {
            visibility: visible;
        }

        #tableToPrint {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Make sure the table is displayed correctly with borders */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th, td {
            font-size: 12pt; /* Ensure font size is appropriate for printing */
        }

        /* Hide the print button */
        .printBtn {
            display: none;
        }

        /* Additional styling for a cleaner print appearance */
        h1 {
            font-size: 18pt; /* Adjust heading size for print */
        }

        /* Ensure the page background is white */
        body {
            background-color: white;
        }

        /* Remove padding/margins that might affect print layout */
        .main-content, .inventory-content {
            margin: 0;
            padding: 0;
        }
        }
    </style>
</head>
<body>

    <div class="main-content" id="main-content">
        <header>
            <h1>Inventory Management</h1>
            <div class="admin-profile">
                <img src="../assets/images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>

        <div class="table-content" id="tableToPrint">
            <div class="table-list">
                <button id="printBtn" onclick="printTable()">  <i class='fas fa-print me-2'></i>Print</button>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Purchases</th>
                            <th>Sales</th>
                            <th>Stocks</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php
                            // Database connection parameters
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

                            // Fetch products from database
                            $sql = "SELECT 
                                        p.product_id, 
                                        p.product_name, 
                                        c.category_name,
                                        p.stocks,
                                        p.threshold,
                                        COALESCE(s.total_sales, 0) AS total_sales,
                                        COALESCE(pr.total_purchases, 0) AS total_purchases
                                    FROM 
                                        products p
                                    LEFT JOIN
                                        category c
                                        ON p.category_id = c.category_id
                                    LEFT JOIN 
                                        (SELECT product_id, SUM(amount) AS total_sales FROM sales_products GROUP BY product_id) s
                                        ON p.product_id = s.product_id
                                    LEFT JOIN 
                                        (SELECT product_id, SUM(purchase_amount) AS total_purchases FROM purchases GROUP BY product_id) pr
                                        ON p.product_id = pr.product_id;
                                    ";

                            $result = $conn->query($sql);

                            // Check if the query was successful
                            if ($result === false) {
                                echo "Error: " . $conn->error;
                            } else {
                                // Check if there are any rows
                                if ($result->num_rows > 0) {
                                    // Output data of each row
                                    while ($row = $result->fetch_assoc()) {
                                        // Set the threshold value for low stock
                                        $stockClass = ($row["stocks"] <= $row["threshold"]) ? "low-stock" : "high-stock";
                                        echo "<tr>
                                                <td>" . htmlspecialchars($row["product_id"]) . "</td>
                                                <td>" . htmlspecialchars($row["product_name"]) . "</td>
                                                <td>" . htmlspecialchars($row["category_name"]) . "</td>
                                                <td>₱" . number_format($row["total_purchases"], 2) . "</td>
                                                <td>₱" . number_format($row["total_sales"], 2) . "</td>
                                                <td><span class='$stockClass'>" . htmlspecialchars($row["stocks"]) . "</span></td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No products found</td></tr>";
                                }
                            }

                            $conn->close();
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>