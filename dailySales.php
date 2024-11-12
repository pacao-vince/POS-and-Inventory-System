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

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$records_per_page = 10;

// Get the cashier username from the query parameter (empty if no cashier is selected)
$cashier_username = isset($_GET['cashier_username']) ? $_GET['cashier_username'] : '';

// Daily Sales Pagination
$daily_sales_page = isset($_GET['daily_sales_page']) ? $_GET['daily_sales_page'] : 1;
$daily_sales_offset = ($daily_sales_page - 1) * $records_per_page;

// Modify the daily sales query based on whether a cashier is selected or not
if ($cashier_username) {
    // If a specific cashier is selected, show their transactions
    $daily_sales_sql = "
        SELECT transaction_time, grand_total 
        FROM sales 
        WHERE DATE(transaction_time) = CURDATE() 
        AND cashier_username = ? 
        ORDER BY transaction_time DESC  
        LIMIT $records_per_page OFFSET $daily_sales_offset";
    $stmt = $conn->prepare($daily_sales_sql);
    $stmt->bind_param('s', $cashier_username);
} else {
    // If no cashier is selected, show all transactions for today
    $daily_sales_sql = "
        SELECT transaction_time, grand_total 
        FROM sales 
        WHERE DATE(transaction_time) = CURDATE() 
        ORDER BY transaction_time DESC  
        LIMIT $records_per_page OFFSET $daily_sales_offset";
    $stmt = $conn->prepare($daily_sales_sql);
}

$stmt->execute();
$daily_sales_result = $stmt->get_result();

// Get all cashiers with their total sales for today
$cashier_sql = "
   SELECT u.username AS cashier_username, 
           COALESCE(SUM(s.grand_total), 0) AS total_sales 
    FROM user_management u 
    LEFT JOIN sales s ON u.username = s.cashier_username 
                      AND DATE(s.transaction_time) = CURDATE() 
    WHERE u.user_type = 'cashier' 
    GROUP BY u.username";
$cashiers_result = $conn->query($cashier_sql);

// Total pages for pagination
if ($cashier_username) {
    $daily_sales_total_sql = "
        SELECT COUNT(grand_total) AS total 
        FROM sales 
        WHERE DATE(transaction_time) = CURDATE() 
        AND cashier_username = ?";
    $stmt_total = $conn->prepare($daily_sales_total_sql);
    $stmt_total->bind_param('s', $cashier_username);
} else {
    $daily_sales_total_sql = "
        SELECT COUNT(grand_total) AS total 
        FROM sales 
        WHERE DATE(transaction_time) = CURDATE()";
    $stmt_total = $conn->prepare($daily_sales_total_sql);
}

$stmt_total->execute();
$daily_sales_total_result = $stmt_total->get_result();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

            <!-- Cashier Cards Section -->
            <section class="cashier-cards">
                <div class="row">
                    <?php while ($cashier = $cashiers_result->fetch_assoc()): ?>
                        <div class="col-md-3">
                            <div class="card cashier-card">
                                <div class="card-body-card">
                                    <h3 class="card-title"><?php echo htmlspecialchars($cashier['cashier_username']); ?></h3>
                                    <p class="card-text">Total Sales: ₱ <?php echo number_format($cashier['total_sales'], 2); ?></p>
                                    <hr class="separator">
                                    <a href="?cashier_username=<?php echo urlencode($cashier['cashier_username']); ?>" class="btn btn-primary">View Sales</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <!-- Daily Sales Section -->
            <section class="daily-sales-report">
                <?php if ($cashier_username): ?>
                    <h2>Daily Sales Report for <?php echo htmlspecialchars($cashier_username); ?></h2>
                <?php else: ?>
                    <h2>Daily Sales Report</h2>
                <?php endif; ?>
                
                <button id="printDailySalesBtn" class="btn btn-success custom-btn"><i class="fas fa-print me-2"></i>Print</button>
                <button id="generateDailySalesBtn" class="btn btn-primary custom-btn-gen"><i class="fas fa-file-alt me-2"></i>Generate Report</button>
            
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
                                        <td>" . htmlspecialchars($row["transaction_time"]) . "</td>
                                        <td>₱ " . number_format($row["grand_total"], 2) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No sales today.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($daily_sales_page > 1): ?>
                        <a href="?daily_sales_page=<?php echo $daily_sales_page - 1; ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $daily_sales_total_pages; $i++): ?>
                        <a href="?daily_sales_page=<?php echo $i; ?>" <?php if ($i == $daily_sales_page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($daily_sales_page < $daily_sales_total_pages): ?>
                        <a href="?daily_sales_page=<?php echo $daily_sales_page + 1; ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="dailySales.js"></script>

</body>
</html>
