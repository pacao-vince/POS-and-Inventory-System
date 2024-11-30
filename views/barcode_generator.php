<?php
require_once '../includes/auth.php';

// Only allow Admin access
if ($_SESSION['user_type'] !== 'admin') {
    logout(); // Call logout to clear the session and redirect
}

include '../includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Barcode Generator</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link rel="stylesheet" href="../assets/css/barcode_generator.css">
</head>
<body>

    <div class="main-content" id="main-content">
        <header>
            <h2></h2>
            <?php include '../views/settings_dropdown.php'; ?>
        </header>
    
        <div class="barcode-content">
            <div class="form-section">
                <h1>Generate Barcode</h1>
                <label for="productName">Product Name:</label>
                <input type="text" id="productName" placeholder="Enter product name" required>
    
                <label for="barcode">Barcode:</label>
                <input type="text" id="barcode" placeholder="Enter barcode" required>
    
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" placeholder="Enter quantity" required>

                <button onclick="generateBarcode()" class="generate"> <i class='fas fa-barcode'></i> GENERATE</button>
                <button onclick="printBarcode()" class="print"> <i class='fas fa-print me-2'></i> PRINT</button>
            </div>
    
            <!-- Result Section -->
            <div class="result-section" id="barcodeContainer">
                <!-- Barcodes will be displayed here -->
            </div>
        </div>

        <script src="../controllers/barcodegen.js"></script>
    </body>
</html>
