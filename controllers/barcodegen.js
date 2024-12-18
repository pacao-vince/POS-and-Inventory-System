function generateBarcode() {
    const barcodeValue = document.getElementById('barcode').value;
    const quantity = document.getElementById('quantity').value;
    const productName = document.getElementById('productName').value;
    const barcodeContainer = document.getElementById('barcodeContainer');
    
    // Clear previous barcodes
    barcodeContainer.innerHTML = '';

    function showAlert(message, type = 'success') {
        const alertBox = document.createElement('div');
        alertBox.className = `alert alert-${type} custom-alert`;
        alertBox.innerText = message;
        document.body.appendChild(alertBox);

        // Add a fade-in effect
        alertBox.style.opacity = '0';
        setTimeout(() => {
            alertBox.style.opacity = '1';
        }, 0); // Delay to allow the element to be added to the DOM

        // Remove alert after 3 seconds
        setTimeout(() => {
            alertBox.style.opacity = '0'; // Start fade-out effect
            setTimeout(() => {
                document.body.removeChild(alertBox); // Remove from DOM after fade-out
            }, 300); // Wait for fade-out to complete
        }, 3000); // Show for 3 seconds
    }

    // Check if inputs are valid
    if (!barcodeValue || quantity <= 0 || !productName) {
        showAlert('Please enter a valid product name, barcode, and quantity.', 'danger');
        return;
    }

    for (let i = 0; i < quantity; i++) {
        // Create a div element to wrap each barcode
        const barcodeBox = document.createElement('div');
        barcodeBox.classList.add('barcode-box');

        barcodeBox.style.height = '100px';
        
        // Add product name above the barcode
        const nameElement = document.createElement('h3');
        nameElement.classList.add('product-name');
        nameElement.textContent = productName;
        barcodeBox.appendChild(nameElement);

        // Create an SVG element for each barcode
        const barcodeSVG = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        barcodeSVG.classList.add('barcode-svg'); // Add a class for styling if needed
        barcodeBox.appendChild(barcodeSVG); // Append the SVG to the div

        // Append the barcode box to the container
        barcodeContainer.appendChild(barcodeBox);

        // Generate the barcode
        JsBarcode(barcodeSVG, barcodeValue, {
            format: 'CODE128',
            width: 2,
            height: 60,
            displayValue: true,
        });
    }
}


function printBarcode() {
    // Get the form section and result section
    var formSection = document.querySelector('.form-section');
    var resultSection = document.querySelector('.result-section');

    // Hide the form section
    formSection.style.display = 'none';

    // Trigger the print dialog
    window.print();

    // Show the form section again after printing
    formSection.style.display = 'block';
}