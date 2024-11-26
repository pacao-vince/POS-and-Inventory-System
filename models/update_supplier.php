<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required POST variables are set
    if (isset($_POST['supplier_id'], $_POST['name'], $_POST['address'], $_POST['contact_num'])) {
        $supplier_id = $_POST['supplier_id'];
        $name = $_POST['name'];
        $address = $_POST['address'];
        $contact_num = $_POST['contact_num'];

        // Prepare an SQL statement for execution
        $stmt = $conn->prepare("UPDATE suppliers SET  name=?, address=?, contact_num=? WHERE supplier_id=?");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters (adjust types as needed)
        $stmt->bind_param("sssi", $name, $address, $contact_num, $supplier_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating supplier: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    }
}

// Close the connection
$conn->close();
?>
