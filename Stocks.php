<?php
/*
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
*/
include_once 'sidebar.html'; 
include_once 'db_connection.php';

// Pagination settings
$records_per_page = 10; // Set the number of records you want to display per page

// Out-of-Stock Products Pagination
$out_of_stock_page = isset($_GET['out_of_stock_page']) ? $_GET['out_of_stock_page'] : 1;
$out_of_stock_offset = ($out_of_stock_page - 1) * $records_per_page;

// Fetch out-of-stock products based on the threshold defined in the products table
$out_of_stock_sql = "SELECT product_id, product_name, stocks 
                     FROM products 
                     WHERE stocks <= threshold 
                     LIMIT $records_per_page OFFSET $out_of_stock_offset";
$out_of_stock_result = $conn->query($out_of_stock_sql);

// Calculate total pages for out-of-stock products
$out_of_stock_total_sql = "SELECT COUNT(*) AS total 
                           FROM products 
                           WHERE stocks <= threshold";
$out_of_stock_total_result = $conn->query($out_of_stock_total_sql);
$out_of_stock_total_data = $out_of_stock_total_result->fetch_assoc();
$out_of_stock_total_pages = ceil($out_of_stock_total_data['total'] / $records_per_page);


// Query to check for products below the threshold
$low_stock_sql = "SELECT product_name, stocks FROM products WHERE stocks <= threshold";
$low_stock_result = $conn->query($low_stock_sql);

// Initialize an array to hold products below the threshold
$lowStockProducts = [];
while ($row = $low_stock_result->fetch_assoc()) {
    $lowStockProducts[] = [
        'name' => $row['product_name'],
        'stocks' => $row['stocks']
    ];
}

// Pass the low stock products to JavaScript as a JSON object
$lowStockJson = json_encode($lowStockProducts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Reports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <!-- Out-of-Stock Products Section -->
            <section class="out-of-stock">
                <h2>Out of Stock Products</h2>
                <button id="printStocksBtn" class="btn btn-success custom-btn">Print</button>
                <button id="generateStocksBtn" class="btn btn-primary custom-btn-gen">Generate Report</button>
                <table class="reportTable">
                <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Stocks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($out_of_stock_result->num_rows > 0) {
                            while ($row = $out_of_stock_result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $row["product_id"] . "</td>
                                        <td>" . $row["product_name"] . "</td>
                                        <td>" . $row["stocks"] . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No out-of-stock products.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <!-- Out-of-Stock Pagination Controls -->
                <div class="pagination">
                    <?php if ($out_of_stock_page > 1): ?>
                        <a href="?out_of_stock_page=<?php echo $out_of_stock_page - 1; ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $out_of_stock_total_pages; $i++): ?>
                        <a href="?out_of_stock_page=<?php echo $i; ?>" <?php if ($i == $out_of_stock_page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($out_of_stock_page < $out_of_stock_total_pages): ?>
                        <a href="?out_of_stock_page=<?php echo $out_of_stock_page + 1; ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>
            </section>

            <?php
                // Query to check for products below the threshold
                $sql = "SELECT product_name, stocks FROM products WHERE stocks < ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $threshold);
                $stmt->execute();
                $result = $stmt->get_result();

                // Initialize an array to hold products below the threshold
                $lowStockProducts = [];

                while ($row = $result->fetch_assoc()) {
                    $lowStockProducts[] = [
                        'name' => $row['product_name'],
                        'stocks' => $row['stocks']
                    ];
                }

                // Pass the low stock products to JavaScript as a JSON object
                $lowStockJson = json_encode($lowStockProducts);
            ?>

        <script>
            // Pass the PHP variable to JavaScript
            var lowStockProducts = <?php echo $lowStockJson; ?>;

            if (lowStockProducts.length > 0) {
                // Iterate through the products and trigger voice notifications
                lowStockProducts.forEach(product => {
                    var message = `Warning! Stock for ${product.name} is below threshold. Only ${product.stocks} left.`;
                    speak(message);
                });
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
        </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="stocks.js"></script>
</body>
</html>

<?php $conn->close(); ?>