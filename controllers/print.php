<?php
require '../vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// Start output buffering
ob_start();

// Set the timezone to your local time
date_default_timezone_set('Asia/Manila');

try {
    // Connect to your database without backticks
    $pdo = new PDO('mysql:host=localhost;dbname=pos&inventory', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate JSON input
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input!']);
        exit;
    }

    // Ensure all required data is present
    if (!isset($data['products'], $data['subTotal'], $data['grandTotal'], $data['payment'], $data['change'], $data['cashier_username'])) {
        echo json_encode(['success' => false, 'message' => 'Error: Missing required data!']);
        exit;
    }

    $products = $data['products'];
    $subTotal = $data['subTotal'];
    $grandTotal = $data['grandTotal'];
    $payment = $data['payment'];
    $change = $data['change'];
    $cashier_username = $data['cashier_username'];

    // Fetch product names from the database using product IDs
    $productDetails = [];
    foreach ($products as $product) {
        $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = :productId");
        $stmt->execute(['productId' => $product['productId']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $productDetails[] = [
                'product_name' => $result['product_name'],
                'qty' => $product['quantity'],
                'price' => $product['price'],
                'amount' => $product['amount']
            ];
        }
    }

    try {
        // Connect to the printer
        $connector = new WindowsPrintConnector("XP58");
        $printer = new Printer($connector);

        // Print store information
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Sheila Store\n");
        $printer->text("San Jose, Camarines Sur\n");
        $printer->text("Tel: 09454834351\n");
        $printer->text(str_repeat("-", 32) . "\n"); // Separator line

        // Print cashier's username
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Cashier: " . $cashier_username . "\n");

        // Print the current date and time in 12-hour format
        $printer->text("Date: " . date('Y-m-d h:i:s A') . "\n");
        $printer->text(str_repeat("-", 32) . "\n"); // Separator line

        // Print column headers
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text(str_pad("Item", 12) . str_pad("Qty", 4, ' ', STR_PAD_LEFT) . str_pad("Price", 8, ' ', STR_PAD_LEFT) . str_pad("Total", 8, ' ', STR_PAD_LEFT) . "\n");
        $printer->text(str_repeat("-", 32) . "\n");

        // Print product details
        foreach ($productDetails as $product) {
            // Increase the width for the item name
            $line = str_pad(substr($product['product_name'], 0, 12), 12); // Trim item name to 12 characters
            $line .= str_pad($product['qty'], 4, ' ', STR_PAD_LEFT);
            $line .= str_pad(number_format($product['price'], 2), 8, ' ', STR_PAD_LEFT);
            $line .= str_pad(number_format($product['amount'], 2), 8, ' ', STR_PAD_LEFT);
            $printer->text($line . "\n");
        }

        $printer->text(str_repeat("-", 32) . "\n");

        // Print totals and payment details
        $printer->setEmphasis(true);
        $printer->text(str_pad("Subtotal:", 24) . str_pad(number_format($subTotal, 2), 8, ' ', STR_PAD_LEFT) . "\n");
        $printer->text(str_pad("Grand Total:", 24) . str_pad(number_format($grandTotal, 2), 8, ' ', STR_PAD_LEFT) . "\n");
        $printer->text(str_pad("Payment:", 24) . str_pad(number_format($payment, 2), 8, ' ', STR_PAD_LEFT) . "\n");
        $printer->text(str_pad("Change:", 24) . str_pad(number_format($change, 2), 8, ' ', STR_PAD_LEFT) . "\n");
        $printer->setEmphasis(false);

        $printer->text(str_repeat("-", 32) . "\n\n");

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true); // Emphasize "THANK YOU" message
        $printer->text("THANK YOU, COME AGAIN!\n\n\n\n");
        $printer->setEmphasis(false); // Remove emphasis

        // Print the disclaimer centered, but without emphasis
        $printer->text("This will not serve as your\n");
        $printer->text("official receipt!\n");

        // Feed some extra lines for spacing
        $printer->feed(2);

        // Cut the receipt
        $printer->cut();
        $printer->close();

        echo json_encode(['success' => true, 'message' => 'Receipt printed successfully!']);
    } catch (Exception $e) {
        // Log the exception message for debugging
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Could not print receipt: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method!']);
}
?>
