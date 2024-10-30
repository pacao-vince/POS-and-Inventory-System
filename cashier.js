document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const searchResultsPopup = document.getElementById('searchResultsPopup');
    const searchResultsBody = document.getElementById('searchResultsBody');
    const productListBody = document.getElementById('productListBody');
    const paymentInput = document.getElementById('payment');
    const changeAmountElement = document.getElementById('changeAmount');
    
    let selectedRow = null;

    // Initialize buttons with correct selectors
    const deletebtn = document.querySelector('.deletebtn');  // Selects by class
    const savebtn = document.querySelector('.savebtn');      // Selects by class
    const confirmSaveSaleBtn = document.getElementById('confirmSaveSaleBtn');
    const newQuantityInput = document.getElementById('newQuantityInput');

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
            alertBox.style.opacity = '0'; // Fade out effect
            setTimeout(() => {
                document.body.removeChild(alertBox);
            }, 300); // Wait for fade-out to complete
        }, 3000); // Remove after 3 seconds
    }

// Event listener for real-time search input
document.getElementById('searchInput').addEventListener('input', function () {
    searchProducts(this.value.trim());
});

// Prevent form submission when Enter is pressed in search input
searchInput.addEventListener('keypress', function (event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Prevent form submission
        searchProducts(searchInput.value.trim()); // Trigger search
    }
});

// Function to search products
function searchProducts(query) {
    if (query.length > 0) {
        fetch(`search_products.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                searchResultsBody.innerHTML = ''; // Clear previous search results, but not the product list
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(product => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${product.product_id}</td>
                            <td>${product.product_name}</td>
                            <td>₱${parseFloat(product.selling_price).toFixed(2)}</td>
                        `;
                        row.addEventListener('click', function () {
                            addToTable(product); // Add selected product to product list
                            searchResultsPopup.style.display = 'none'; // Hide search results popup
                        });
                        searchResultsBody.appendChild(row);
                    });
                    searchResultsPopup.style.display = 'block'; // Show search results
                } else {
                    searchResultsPopup.style.display = 'none'; // No results found
                }
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                showAlert('An error occurred while searching for products.', 'danger'); // Alert for fetch error
            });
    } else {
        searchResultsPopup.style.display = 'none'; // Hide search popup if query is empty
    }
}

  // Function to add selected product to the table
function addToTable(product) {
    const existingRow = Array.from(productListBody.rows).find(row => row.cells[0].textContent === product.product_id.toString());
    
    if (existingRow) {
        const quantityCell = existingRow.cells[2];
        quantityCell.textContent = parseInt(quantityCell.textContent) + 1; // Increment quantity
    } else {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${product.product_id}</td>
            <td>${product.product_name}</td>
            <td>1</td>
            <td>₱${parseFloat(product.selling_price).toFixed(2)}</td>
            <td>₱${parseFloat(product.selling_price).toFixed(2)}</td>
        `;
        productListBody.appendChild(newRow);
        newRow.addEventListener('click', function () {
            highlightRow(newRow);
        });
    }

    // Clear the search input and hide search results
    searchInput.value = '';
    searchResultsPopup.style.display = 'none';
    
    updateTotals();
}

    // Function to update totals
    window.updateTotals = function() {
        let subTotal = 0;
        Array.from(productListBody.rows).forEach(row => {
            const quantity = parseInt(row.cells[2].textContent);
            const price = parseFloat(row.cells[3].textContent.replace('₱', '').replace(/,/g, ''));
            const amount = quantity * price;
            row.cells[4].textContent = `₱${amount.toFixed(2)}`;
            subTotal += amount;
        });
        document.getElementById('subTotal').textContent = `₱${subTotal.toFixed(2)}`;
        document.getElementById('grandTotal').textContent = `₱${subTotal.toFixed(2)}`;
        calculateChange();
    };
    
    // Function to calculate change
    function calculateChange() {
        const grandTotal = parseFloat(document.getElementById('grandTotal').textContent.replace('₱', '').replace(/,/g, ''));
        const payment = parseFloat(paymentInput.value.trim());
        const change = payment - grandTotal;
        changeAmountElement.textContent = formatCurrency(change > 0 ? change : 0);
    }

    // Format numbers as currency
    function formatCurrency(amount) {
        return `₱${parseFloat(amount).toFixed(2)}`;
    }

    paymentInput.addEventListener('input', calculateChange);

    
// Highlight or deselect the row
function toggleRowHighlight(row) {
    if (row.classList.contains('table-primary')) {
        // If the row is already selected, deselect it
        row.classList.remove('table-primary');
        selectedRow = null; // Clear the selection
    } else {
        // If another row is selected, deselect it first
        if (selectedRow) {
            selectedRow.classList.remove('table-primary');
        }
        // Select the new row
        selectedRow = row;
        selectedRow.classList.add('table-primary');
    }
}

productListBody.addEventListener('click', function(event) {
    console.log('Row clicked', event.target); // Log what was clicked
    const targetRow = event.target.closest('tr');
    if (targetRow && targetRow.parentElement === productListBody) {
        toggleRowHighlight(targetRow); // Call the function to toggle highlight
        console.log('Row selected:', selectedRow); // Log the selected row for debugging
    } else {
        console.log('No valid row clicked');
    }
});

    
  document.querySelector('.updatebtn').addEventListener('click', function() {
        if (selectedRow) {
            $('#qtyModal').modal('show');
            newQuantityInput.value = selectedRow.cells[2].textContent; // Set current quantity
        } else {
            showAlert('Please select a product row first.', 'danger'); // Alert for no selection
        }   
    });
    
    // Confirm update quantity
    document.getElementById('updateQtyBtn').addEventListener('click', function () {
        const newQuantity = parseInt(newQuantityInput.value);
        if (newQuantity > 0 && selectedRow) {
            selectedRow.cells[2].textContent = newQuantity;
            updateTotals();
            $('#qtyModal').modal('hide'); // Hide modal after updating
        } else {
            showAlert('Invalid quantity', 'danger'); // Alert for invalid quantity
        }
    });

    // Delete selected product
    deletebtn.addEventListener('click', function () {
        console.log('Delete button clicked. Selected row:', selectedRow); // Debugging log
        if (selectedRow) {
            $('#deleteConfirmationModal').modal('show'); // Show delete confirmation modal
        } else {
            showAlert('Please select a product to delete.', 'danger'); // Alert for no selection
          return;
        }
    });

    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (selectedRow) {
            selectedRow.remove(); // Remove row
            updateTotals(); // Update totals
            $('#deleteConfirmationModal').modal('hide');
            showAlert('Product deleted successfully!'); // Alert for successful deletion
        }
    });

    // Open the save sale modal 
    savebtn.addEventListener('click', function () {
        const paymentInput = document.getElementById('payment').value.trim(); // Get payment input value
    
        if (productListBody.rows.length === 0) {
            showAlert('No products to save.', 'danger'); // Alert for no products
        } else if (!paymentInput || isNaN(paymentInput) || parseFloat(paymentInput) <= 0) {
            showAlert('Please enter a valid payment amount.', 'danger'); // Alert for invalid payment
        } else {
            $('#saveSaleConfirmationModal').modal('show'); // Open modal only if payment is valid
        }
    });
    
    // Confirm save sale
confirmSaveSaleBtn.addEventListener('click', function () {
    const data = {
        subTotal: parseFloat(document.getElementById('subTotal').textContent.replace('₱', '')),
        grandTotal: parseFloat(document.getElementById('grandTotal').textContent.replace('₱', '')),
        payment: parseFloat(paymentInput.value.trim()),
        change: parseFloat(changeAmountElement.textContent.replace('₱', '')),
        transactionTime: new Date().toISOString(),
        cashier_username: document.querySelector('.cashier-profile span').textContent.replace('Cashier, ', '').trim(),
        products: Array.from(productListBody.querySelectorAll('tr')).map(row => ({
            productId: parseInt(row.cells[0].textContent),
            productName: row.cells[1].textContent,
            qty: parseInt(row.cells[2].textContent),
            price: parseFloat(row.cells[3].textContent.replace('₱', '')),
            amount: parseFloat(row.cells[4].textContent.replace('₱', ''))
        }))
    };

    // Hide the modal before saving
    $('#saveSaleConfirmationModal').modal('hide');

    // Call save sale function
    window.saveSale(data).then(() => {
        // Automatically print the receipt after saving
        printReceipt(data);
    }).catch(err => {
        console.error('Error saving sale:', err);
        showAlert('Error saving sale. Please try again.', 'danger');
    });
});

    // Function to update date and time
    function updateDateTime() {
        const now = new Date();
        const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('date-time').innerText = now.toLocaleDateString('en-US', options);
    }
    
    setInterval(updateDateTime, 1000); // Update every second
    document.getElementById('cashierProfile').addEventListener('click', function() {
        var dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    });

    document.addEventListener('keydown', function(event) {
        const modalButtons = document.querySelectorAll('.modal-footer .btn');
        let highlightedButtonIndex = Array.from(modalButtons).findIndex(button => button.classList.contains('highlight'));
    
        // Navigate between buttons with arrow keys
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            if (highlightedButtonIndex === -1) {
                // If no button is highlighted, start with the first one
                highlightedButtonIndex = 0;
            } else {
                // Remove highlight from the current button
                modalButtons[highlightedButtonIndex].classList.remove('highlight');
                
                // Determine the new index
                if (event.key === 'ArrowLeft') {
                    highlightedButtonIndex = highlightedButtonIndex > 0 ? highlightedButtonIndex - 1 : modalButtons.length - 1;
                } else if (event.key === 'ArrowRight') {
                    highlightedButtonIndex = (highlightedButtonIndex + 1) % modalButtons.length;
                }
            }
    
            // Highlight the new button
            modalButtons[highlightedButtonIndex].classList.add('highlight');
            modalButtons[highlightedButtonIndex].focus(); // Focus on the highlighted button for better UX
        }
    
        // Trigger the highlighted button with the Enter key
        if (event.key === 'Enter' && highlightedButtonIndex !== -1) {
            modalButtons[highlightedButtonIndex].click(); // Simulate a click on the highlighted button
        }
    
        // Ctrl + U for Update Quantity
        if (event.ctrlKey && event.key === 'u') {
            event.preventDefault(); // Prevent default action
            document.querySelector('.updatebtn').click(); // Trigger the Update Quantity button
        }
        // Ctrl + D for Delete
        else if (event.ctrlKey && event.key === 'd') {
            event.preventDefault(); 
            document.querySelector('.deletebtn').click(); // Trigger the Delete button
        }
        // Ctrl + S for Save Sale
        else if (event.ctrlKey && event.key === 's') {
            event.preventDefault(); 
            document.querySelector('.savebtn').click(); // Trigger the Save Sale button
        }
    });
    
     
})
