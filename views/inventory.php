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
            // Display the current date in the print header
            const currentDate = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('printDate').innerText = `Date: ${currentDate.toLocaleDateString('en-US', options)}`;
            
            // Hide elements that shouldn't appear in the printout
            document.getElementById('printBtn').style.display = 'none';
            document.getElementById('sidebar').style.display = 'none';
            document.querySelector('header').style.display = 'none';

            // Trigger print
            window.print();

            // Restore the visibility of hidden elements after printing
            document.getElementById('printBtn').style.display = 'block';
            document.getElementById('sidebar').style.display = 'flex';
            document.querySelector('header').style.display = 'flex';
        }
    </script>
    <style>
        /* Print Button Style */
        #printBtn {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            padding: 5px 8px;
            font-size: 1.6rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 4px;
            margin-bottom: 20px;
            float: right;
        }

        #printBtn:hover {
            background-color: #218838;
        }

        .print-header {
            display: none; 
        }

        .print-header h1 {
            font-size: 16pt;
            font-weight: bold; 
            margin: 5px 0; 
        }

        #printDate {
            font-size: 14pt; 
            font-weight: normal; 
            margin-top: 5px; 
        }

        @media print {
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

            .print-header {
                display: block; 
                text-align: center;
                font-size: 16pt;
                margin-bottom: 10px;
                font-weight: bold;
            }

            /* Ensure the table is displayed correctly with borders */
            table {
                width: 100%;
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid black !important;
            }

            table tr:first-child {
                background-color: white !important;
            }

            th, td {
                font-size: 12pt; /* Standardize font size for printing */
                color: black;    /* Remove color for text */
                background-color: transparent; /* Ensure a clean appearance */
            }

            /* Remove the print button */
            #printBtn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="main-content" id="main-content">
        <header>
            <h1>Inventory Management</h1>
            <?php include '../views/settings_dropdown.php'; ?>
        </header>

        <div class="table-content" id="tableToPrint">
            <!-- Store Name and Date -->
            <div class="print-header">
                <h1>Sheila Grocery Store</h1>
                <h1>Inventory List</h1>
                <p id="printDate"></p>
            </div>

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
                        include '../includes/db_connection.php'; 
                        
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
                                    ON p.product_id = pr.product_id;";

                        $result = $conn->query($sql);

                        if ($result === false) {
                            echo "Error: " . $conn->error;
                        } else {
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row["product_id"]) . "</td>
                                            <td>" . htmlspecialchars($row["product_name"]) . "</td>
                                            <td>" . htmlspecialchars($row["category_name"]) . "</td>
                                            <td>₱" . number_format($row["total_purchases"], 2) . "</td>
                                            <td>₱" . number_format($row["total_sales"], 2) . "</td>
                                            <td>" . htmlspecialchars($row["stocks"]) . "</td>
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
