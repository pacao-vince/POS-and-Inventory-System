<?php
header('Content-Type: application/json');

require_once '../includes/db_connection.php';

// Process form submission for adding a new purchase
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supplier'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact_num = $_POST['contact_num'];

    try{
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO suppliers (name, address, contact_num) VALUES  (?, ?, ?)");
        $stmt->bind_param("sss", $name, $address, $contact_num);

        // Execute the query
        if ($stmt->execute()) {
            // If the category was successfully added
            echo json_encode(['success' => true, 'message' => 'Supplier added successfully.']);
        } else {
            // If there was an error executing the query
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }catch (Exception $e) {
        // Catch any unexpected exceptions
        echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
    }        
} else {
    // If it's not a POST request, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
    
    exit;
?>
