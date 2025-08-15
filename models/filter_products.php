<?php
require_once '../includes/db_connection.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

// Build WHERE clause
$whereConditions = [];
if (!empty($search)) {
    $whereConditions[] = "p.product_name LIKE '%$search%'";
}
if (!empty($category)) {
    $whereConditions[] = "c.category_name = '$category'";
}
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Fetch filtered products
$sql = "SELECT p.product_id, p.product_name, p.barcode, c.category_name, 
               p.buying_price, p.selling_price, p.stocks, p.threshold
        FROM products p
        LEFT JOIN category c ON p.category_id = c.category_id
        $whereClause
        ORDER BY p.product_id DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stockClass = ($row["stocks"] <= $row["threshold"]) ? "low-stock" : "high-stock";
        echo "<tr data-product-id='" . $row["product_id"] . "'>
                <td>" . $row["product_id"] . "</td>
                <td class='product-name'>" . $row["product_name"] . "</td>
                <td>" . $row["barcode"] . "</td>
                <td>" . $row["category_name"] . "</td>
                <td>₱" . number_format($row["buying_price"], 2) . "</td>
                <td>₱" . number_format($row["selling_price"], 2) . "</td>
                <td><span class='$stockClass'>" . $row["stocks"] . "</span></td>
                <td>" . number_format($row["threshold"]) . "</td>
                <td>
                    <button class='btn btn-success editBtn' data-id='" . $row['product_id'] . "'><i class='fas fa-edit me-2'></i>Edit</button> |
                    <button class='btn btn-danger deleteBtn' data-id='" . $row['product_id'] . "'><i class='fas fa-trash me-2'></i>Delete</button>
                </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No products found</td></tr>";
}

$conn->close();
?>
