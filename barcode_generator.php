<?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

    if ($_SESSION['user_type'] !== 'admin') {
        header('Location: login.php');
        exit();
    }

    include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Barcode Generator</title>
    
    <link rel="stylesheet" href="barcode_generator.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

    <div class="main-content" id="main-content">
        <header>
            <h2></h2>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
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

        <script src="barcodegen.js"></script>
    </body>
</html>
