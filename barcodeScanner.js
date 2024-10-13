document.addEventListener('DOMContentLoaded', function () {
    let barcodeInput = '';
    let barcodeTimeout;
    const barcodeProcessingTime = 100; // Time to wait before processing input as barcode

    document.addEventListener('keydown', function (event) {
        const key = event.key;

        if (key.length === 1 && !event.ctrlKey && !event.metaKey) {
            barcodeInput += key;

            clearTimeout(barcodeTimeout);
            barcodeTimeout = setTimeout(() => {
                if (barcodeInput.length > 5) { // Adjust based on your barcode length
                    console.log('Processing barcode:', barcodeInput);
                    processBarcode(barcodeInput);
                }
                barcodeInput = '';  // Reset the input
            }, barcodeProcessingTime);
        }

        if (key === 'Enter') {
            clearTimeout(barcodeTimeout); // Ensure no delayed processing after Enter is pressed
            if (barcodeInput) {
                console.log('Processing barcode:', barcodeInput);
                processBarcode(barcodeInput);
                barcodeInput = '';  // Reset the input for the next barcode
            }
        }
    });

    function processBarcode(barcode) {
        fetch(`cashier.php?barcode=${encodeURIComponent(barcode)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Barcode data received:', data);
                if (data && Object.keys(data).length > 0) {
                    addProductToTable(data);
                } else {
                    alert('Product not found');
                }
            })
            .catch(error => {
                console.error('Error fetching or parsing data:', error);
                alert('An error occurred while processing the barcode.');
            });
    }

    function addProductToTable(product) {
        const tableBody = document.querySelector('#productListBody');
        let existingRow = null;

        tableBody.querySelectorAll('tr').forEach(row => {
            if (row.cells[0].textContent === product.product_id.toString()) {
                existingRow = row;
            }
        });

        if (existingRow) {
            const quantityCell = existingRow.cells[2];
            const amountCell = existingRow.cells[4];

            const currentQuantity = parseInt(quantityCell.textContent);
            const newQuantity = currentQuantity + 1;
            const newAmount = newQuantity * parseFloat(product.selling_price);

            quantityCell.textContent = newQuantity;
            amountCell.textContent = `₱${newAmount.toFixed(2)}`;
        } else {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${product.product_id}</td>
                <td>${product.product_name}</td>
                <td>1</td>
                <td>₱${parseFloat(product.selling_price).toFixed(2)}</td>
                <td>₱${parseFloat(product.selling_price).toFixed(2)}</td>
            `;
            tableBody.appendChild(newRow);
        }

        updateTotals();
    }

    function updateTotals() {
        const tableBody = document.querySelector('#productListBody');
        const rows = tableBody.querySelectorAll('tr');
        let subTotal = 0;

        rows.forEach(row => {
            const amountText = row.cells[4].textContent.replace('₱', '').trim();
            const amount = parseFloat(amountText);

            if (!isNaN(amount)) {
                subTotal += amount;
            }
        });

        document.querySelector('#subTotal').textContent = `₱${subTotal.toFixed(2)}`;
        document.querySelector('#grandTotal').textContent = `₱${subTotal.toFixed(2)}`;
    }
});
