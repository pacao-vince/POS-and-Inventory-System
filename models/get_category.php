<?php
include_once '../includes/db_connection.php';

// Fetch product details
if (isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT category_id, category_name FROM category WHERE category_id=?");
    $stmt->bind_param("i", $categoryId);

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Output product details as JSON
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["Error" => "Category not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
