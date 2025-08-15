<?php
// filter_sales.php
require_once '../includes/db_connection.php';

// Get the cashier filter, default to 'All'
$cashier = isset($_GET['cashier']) ? $_GET['cashier'] : 'All';

// Base query for sales data
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
        LEFT JOIN user_management ON sales.user_id = user_management.user_id";

// Add cashier filter if applicable
if ($cashier !== 'All') {
    $sql .= " WHERE user_management.username = ?";
}

$sql .= " ORDER BY sales.sale_id DESC";

$stmt = $conn->prepare($sql);

if ($cashier !== 'All') {
    $stmt->bind_param("s", $cashier);
}

$stmt->execute();
$result = $stmt->get_result();

$output = '';

// Loop through results
while ($row = $result->fetch_assoc()) {
    // Format transaction time
    $transactionTime12Hour = (new DateTime($row['transaction_time']))->format('m/d/Y h:i A');
    
    // Generate table row
    $output .= "<tr>
                    <td>" . htmlspecialchars($row['sale_id']) . "</td>
                    <td class='sale-date'>" . htmlspecialchars($transactionTime12Hour) . "</td>
                    <td class='product-name'>" . htmlspecialchars($row['product_name']) . "</td>
                    <td>" . htmlspecialchars($row['quantity']) . "</td>
                    <td>₱ " . htmlspecialchars($row['amount']) . "</td>
                    <td>₱ " . htmlspecialchars($row['payment']) . "</td>
                    <td>₱ " . htmlspecialchars($row['change_amount']) . "</td>
                    <td class='cashier'>" . htmlspecialchars($row['username']) . "</td>
                </tr>";
}

// Handle no results case
if ($output === '') {
    $output = "<tr><td colspan='8'>No sales found.</td></tr>";
}

// Output the results
echo $output;

// Close connection
$conn->close();
?>
