<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supplier_id'])) {
    $supplier_id = $_POST['supplier_id'];

    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id=?");
    if ($stmt) {
        $stmt->bind_param("i", $supplier_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting supplier: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
