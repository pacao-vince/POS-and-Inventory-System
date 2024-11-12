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
include_once 'db_connection.php';
include 'sidebar.php';

// Pagination settings
$records_per_page = 10; // Set the number of records you want to display per page

// Monthly Sales Summary Pagination
$monthly_sales_page = isset($_GET['monthly_sales_page']) ? $_GET['monthly_sales_page'] : 1;
$monthly_sales_offset = ($monthly_sales_page - 1) * $records_per_page;

$monthly_sales_summary_sql = "
    SELECT 
        YEAR(transaction_time) AS year, 
        MONTH(transaction_time) AS month, 
        SUM(grand_total) AS total_sales 
    FROM sales 
    WHERE transaction_time IS NOT NULL AND YEAR(transaction_time) > 0
    GROUP BY YEAR(transaction_time), MONTH(transaction_time)
    ORDER BY YEAR(transaction_time) DESC, MONTH(transaction_time) DESC
    LIMIT $records_per_page OFFSET $monthly_sales_offset";
$monthly_sales_summary_result = $conn->query($monthly_sales_summary_sql);

// Calculate total pages for Monthly Sales Summary
$monthly_sales_total_sql = "SELECT COUNT(DISTINCT YEAR(transaction_time), MONTH(transaction_time)) AS total FROM sales WHERE transaction_time IS NOT NULL AND YEAR(transaction_time) > 0";
$monthly_sales_total_result = $conn->query($monthly_sales_total_sql);
$monthly_sales_total_data = $monthly_sales_total_result->fetch_assoc();
$monthly_sales_total_pages = ceil($monthly_sales_total_data['total'] / $records_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Reports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <section class="monthly-sales-report">
                <h2>Monthly Sales Summary</h2>
                <button id="printMonthlySalesBtn" class="btn btn-success custom-btn"><i class="fas fa-print me-2"></i> Print</button>
                <button id="generateMonthlySalesBtn" class="btn btn-primary custom-btn-gen"><i class="fas fa-file-alt me-2"></i>Generate Report</button>
                <table class="reportTable">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($monthly_sales_summary_result->num_rows > 0) {
                            while ($row = $monthly_sales_summary_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $row["year"] . "</td>
                                        <td>" . date('F', mktime(0, 0, 0, $row["month"], 10)) . "</td>
                                        <td>₱ " . number_format($row["total_sales"], 2) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No monthly sales summary available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <!-- Monthly Sales Pagination Controls -->
                <div class="pagination">
                    <?php if ($monthly_sales_page > 1): ?>
                        <a href="?monthly_sales_page=<?php echo $monthly_sales_page - 1; ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $monthly_sales_total_pages; $i++): ?>
                        <a href="?monthly_sales_page=<?php echo $i; ?>" <?php if ($i == $monthly_sales_page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($monthly_sales_page < $monthly_sales_total_pages): ?>
                        <a href="?monthly_sales_page=<?php echo $monthly_sales_page + 1; ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="monthlySales.js"></script>
</body>
</html>