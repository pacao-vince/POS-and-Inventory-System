<?php 
/*
    include('auth.php');
    include 'sidebar.php'; 
    // Only allow Admin access
    if ($_SESSION['user_type'] !== 'admin') {
        header('Location: login.php');
        exit();
    }*/
    include 'sidebar.php'; 
    include 'db_connection.php';

    // Path for notification tracking file
    $notificationFile = 'notifications.json';

    // Load existing notifications or initialize
    $notifications = file_exists($notificationFile) ? json_decode(file_get_contents($notificationFile), true) : [];

    // Get today's date
    $today = date('Y-m-d');
        // Query to check for products below the threshold
    $low_stock_sql = "SELECT product_id, product_name, stocks, threshold FROM products WHERE stocks <= threshold";
    $low_stock_result = $conn->query($low_stock_sql);

    // Initialize an array to hold products below the threshold
    $lowStockProducts = [];
    while ($row = $low_stock_result->fetch_assoc()) {
        // Check if notification has already been sent today for this product
        $productId = $row['product_id'];
        if (!isset($notifications[$productId]) || $notifications[$productId] !== $today) {
            // Update notifications to mark as notified today
            $notifications[$productId] = $today;
            // Add to low stock products array
            $lowStockProducts[] = [
                'name' => $row['product_name'],
                'stocks' => $row['stocks']
            ];
            $alertAlreadySent = true; // Set the flag to true if we are notifying about low stock
        }
    }

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
                
                <div class="stat-item"  data-href="dailySales.php"> 
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
                    <div class="stat-icon">
                        <img src="images/sales-up-graph-svgrepo-com.png" alt="Sales Icon">
                    </div>
                </div>

                <div class="stat-item"  data-href="categories.php">
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
                    <div class="stat-icon">
                        <img src="images/category-svgrepo-com.png" alt="Categories Icon">
                    </div>
                </div>

                <div class="stat-item"  data-href="monthlySales.php">
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
                    <div class="stat-icon">
                        <img src="images/monthlySales.png" alt="Monthly Sales Icon">
                    </div>
                </div>

                <div class="stat-item"  data-href="products.php">
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
                    <div class="stat-icon">
                        <img src="images/product-svgrepo-com.png" alt="Products Icon">
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

    <!-- Modal for low stock alert -->
    <div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="lowStockModalLabel">Low Stock Alert</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p id="lowStockMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <a href="stocks.php" class="btn btn-primary">Go to Stocks Page</a>
        </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
        // Pass the PHP variable to JavaScript
            var lowStockProducts = <?php echo json_encode($lowStockProducts); ?>; 

            if (lowStockProducts.length > 0) {
                // Create the message for low stock products
                var message = "Warning! The following stock levels are low:\n";
                lowStockProducts.forEach(product => {
                    message += `${product.name}: Only ${product.stocks} left.\n`;
                    speak(`Warning! Stock for ${product.name} is below threshold. Only ${product.stocks} left.`);
                });

                // Set the message in the modal
                document.getElementById("lowStockMessage").innerText = message;

                // Show the modal
                var lowStockModal = new bootstrap.Modal(document.getElementById('lowStockModal'));
                lowStockModal.show();
            }

            // Function to speak a message using Web Speech API
            function speak(message) {
                var speech = new SpeechSynthesisUtterance(message);
                speech.pitch = 2;  // Set the pitch (range: 0 to 2)
                speech.rate = 1;   // Set the rate of speech (range: 0.1 to 10)
                speech.voice = window.speechSynthesis.getVoices()[0]; // Set a specific voice
                speech.lang = 'en-US'; // Set the language
                window.speechSynthesis.speak(speech);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>
<?php $conn->close(); ?>


