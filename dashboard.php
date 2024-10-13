<?php 
include 'sidebar.html'; 
    /*
    include('auth.php');
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
        */
    $username = "root"; 
    $password = ""; 
    $database = "pos&inventory"; 
    $conn = new mysqli("localhost", $username, $password, $database); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="dashboard.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <div class="main-content" id="main-content">
        <header>
            <h1>Dashboard</h1>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
            
        <div class="dashboard-content">
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <img src="images/sales-up-graph-svgrepo-com.png" alt="Sales Icon">
                    </div>
                    <div class="stat-info">
                        
                        <h2>
							<?php
								// Get total sales for the current day
                            $currentDate = date('Y-m-d');
                            $daily_sales_sql = "SELECT SUM(amount) AS daily_sales FROM sales_products 
                                                    JOIN sales ON sales_products.sale_id = sales.sale_id
                                                    WHERE DATE(sales.transaction_time) = '$currentDate'";
                                                    
                            $daily_sales_result = $conn->query($daily_sales_sql);

                            if ($daily_sales_result) {
                                $daily_sales_row = $daily_sales_result->fetch_assoc();
                                $daily_sales = $daily_sales_row['daily_sales'] ? $daily_sales_row['daily_sales'] : 0;
                                echo "₱ " . number_format($daily_sales, 2);
                            } else {
                                echo "₱ 0.00";
                            }
							?>
						</h2>
                        <p>Daily Sales</p>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <img src="images/category-svgrepo-com.png" alt="Categories Icon">
                    </div>
                    <div class="stat-info">
                        <?php
                            $query = "SELECT COUNT(*) AS numberOfCategories FROM category;";

                                    if ($result = $conn->query($query)) {
                                        while ($row = $result->fetch_assoc()) {
                                            $numberOfCategories = $row["numberOfCategories"];
                                                        
                                            echo '<h2>' .$numberOfCategories. '</h2>';
                                        }}
                            
                        ?>
                        <p>Categories</p>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <img src="images/monthlySales.png" alt="Products Icon">
                    </div>
                    <div class="stat-info">
                    <h2>
                        <?php
                                // Get total sales for the current month
                            $currentYear = date('Y');
                            $currentMonth = date('m');
                            $monthly_sales_sql = "SELECT SUM(amount) AS monthly_sales FROM sales_products 
                                                    JOIN sales ON sales_products.sale_id = sales.sale_id
                                                    WHERE YEAR(sales.transaction_time) = '$currentYear'
                                                    AND MONTH(sales.transaction_time) = '$currentMonth'";

                            $monthly_sales_result = $conn->query($monthly_sales_sql);

                            if ($monthly_sales_result) {
                                $monthly_sales_row = $monthly_sales_result->fetch_assoc();
                                $monthly_sales = $monthly_sales_row['monthly_sales'] ? $monthly_sales_row['monthly_sales'] : 0;
                                echo "₱ " . number_format($monthly_sales, 2);
                            } else {
                                echo "₱ 0.00";
                            }
                        ?>
                    </h2>
                        <p>Monthly Sales</p>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <img src="images/product-svgrepo-com.png" alt="Products Icon">
                    </div>
                    <div class="stat-info">
                        <?php
                                $sql = "SELECT COUNT(*) AS total_products FROM products";
                                $result = $conn->query($sql);
                                $total_products = 0;
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $total_products = $row['total_products'];
                                }
                                echo '<h2>'.$total_products.'</h2>';
                        ?>
                        <p>Products</p>
                    </div>
                </div>
            </div>

            <div class="charts">
                <div class="sales-graph">
                    <h2>Daily Sales Graph</h2>
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="bestseller-products">
                    <h2>Best Seller Products</h2>
                    <canvas id="bestsellerChart"></canvas>
                </div>
            </div>
            <div class="recently-added-products">
                <h2>Recently Added Products</h2>
                <ul>
                    <li>
						<?php
							$query = "SELECT * FROM products ORDER BY product_id DESC LIMIT 10";
							$result = $conn->query($query);

							// Check if the query succeeded
							if ($result) {
								if ($result->num_rows > 0) {
									// Output data of each row
									while ($row = $result->fetch_assoc()) {
										 
											echo '<li>
												' . htmlspecialchars($row["product_name"]) . '
												<span>₱' . htmlspecialchars($row["selling_price"]) . '</span>
											</li>';
										
									}
								} else {
									echo "0 results";
								}
							} else {
								// Output query error if query fails
								echo "Error: " . $conn->error;
							}
						?>
					</li>
                </ul>
            </div>
        </div>
    </div>
    <script src="dashboard.js"></script>

</body>
</html>
<?php
$conn->close();
?>
