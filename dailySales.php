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

include 'sidebar.html';

// Pagination settings
$records_per_page = 10; // Set the number of records you want to display per page


// Daily Sales Pagination
$daily_sales_page = isset($_GET['daily_sales_page']) ? $_GET['daily_sales_page'] : 1;
$daily_sales_offset = ($daily_sales_page - 1) * $records_per_page;

$daily_sales_sql = "SELECT transaction_time, grand_total FROM sales WHERE DATE(transaction_time) = CURDATE() ORDER BY transaction_time DESC  LIMIT $records_per_page OFFSET $daily_sales_offset";
$daily_sales_result = $conn->query($daily_sales_sql);

// Calculate total pages for Daily Sales
$daily_sales_total_sql = "SELECT COUNT(grand_total) AS total FROM sales WHERE DATE(transaction_time) = CURDATE()";
$daily_sales_total_result = $conn->query($daily_sales_total_sql);
$daily_sales_total_data = $daily_sales_total_result->fetch_assoc();
$daily_sales_total_pages = ceil($daily_sales_total_data['total'] / $records_per_page);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Reports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="reports.css">
</head>
<body>

    <div class="main-content" id="main-content">
        <header>
            <h1>Reports</h1>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
        <div class="reports-content">

        <section class="daily-sales-report">
                <h2>Daily Sales Report</h2>
                <button id="printDailySalesBtn" class="btn btn-success custom-btn">Print</button>
                <button id="generateDailySalesBtn" class="btn btn-primary custom-btn-gen">Generate Report</button>
                <table class="reportTable">
                    <thead>
                        <tr>
                            <th>Transaction Time</th>
                            <th>Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($daily_sales_result->num_rows > 0) {
                            while ($row = $daily_sales_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $row["transaction_time"] . "</td>
                                        <td>₱ " . number_format($row["grand_total"], 2) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No sales today.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <!-- Out-of-Stock Pagination Controls -->
                <div class="pagination">
                    <?php if ($daily_sales_page > 1): ?>
                        <a href="?daily_sales_page=<?php echo $daily_sales_page - 1; ?>">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $daily_sales_total_pages; $i++): ?>
                        <a href="?daily_sales_page=<?php echo $i; ?>" <?php if ($i == $daily_sales_page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($daily_sales_page < $daily_sales_total_pages): ?>
                        <a href="?daily_sales_page=<?php echo $daily_sales_page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
        </section>   

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script src="dailySales.js"></script>

</body>
</html>