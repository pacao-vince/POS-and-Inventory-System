document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const searchResultsPopup = document.getElementById('searchResultsPopup');
    const searchResultsBody = document.getElementById('searchResultsBody');
    const productListBody = document.getElementById('productListBody');
    const paymentInput = document.getElementById('payment');
    const changeAmountElement = document.getElementById('changeAmount');
    
    let selectedRow = null;
    let selectedSearchResultRow = null;

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

document.addEventListener('keydown', function (event) {
    if (event.ctrlKey && event.key === 'f') {
        event.preventDefault(); // Prevent the default browser search
        searchInput.focus(); // Focus on the search input
        searchInput.select(); // Optionally, select any existing text in the input
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
                    selectedSearchResultRow = searchResultsBody.firstElementChild; // Select the first row by default
                    highlightSearchResultRow(selectedSearchResultRow);
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

// Function to highlight the current search result row
function highlightSearchResultRow(row) {
    Array.from(searchResultsBody.children).forEach(r => r.classList.remove('table-primary')); // Remove highlight from all rows
    if (row) {
        row.classList.add('table-primary'); // Highlight the selected row
    }
}

// Event listener for keydown on the search input for arrow navigation and Enter to add to table
searchInput.addEventListener('keydown', function (event) {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'Enter') {
        event.stopPropagation(); // Prevent the event from affecting other tables
    }
   
    if (event.key === 'ArrowDown') {
        event.preventDefault(); // Prevent default scrolling
        if (selectedSearchResultRow && selectedSearchResultRow.nextElementSibling) {
            selectedSearchResultRow = selectedSearchResultRow.nextElementSibling;
        } else {
            selectedSearchResultRow = searchResultsBody.firstElementChild; // Wrap to the top if at the bottom
        }
        highlightSearchResultRow(selectedSearchResultRow);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault(); // Prevent default scrolling
        if (selectedSearchResultRow && selectedSearchResultRow.previousElementSibling) {
            selectedSearchResultRow = selectedSearchResultRow.previousElementSibling;
        } else {
            selectedSearchResultRow = searchResultsBody.lastElementChild; // Wrap to the bottom if at the top
        }
        highlightSearchResultRow(selectedSearchResultRow);
    } else if (event.key === 'Enter') {
        event.preventDefault(); // Prevent form submission
        if (selectedSearchResultRow) {
            // Find the product based on the row's data
            const productId = selectedSearchResultRow.cells[0].textContent;
            const productName = selectedSearchResultRow.cells[1].textContent;
            const productPrice = parseFloat(selectedSearchResultRow.cells[2].textContent.replace('₱', ''));
            const product = { product_id: productId, product_name: productName, selling_price: productPrice };
            addToTable(product); // Add selected product to product list
            searchResultsPopup.style.display = 'none'; // Hide search results
        }
    }
});

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
        const targetRow = event.target.closest('tr');
        if (targetRow && targetRow.parentElement === productListBody) {
            toggleRowHighlight(targetRow); // Call the function to toggle highlight
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

   // Event listener for the delete button
deletebtn.addEventListener('click', function () {
    console.log('Delete button clicked. Selected row:', selectedRow); // Debugging log
    if (selectedRow) {
        $('#adminAuthModal').modal('show'); // Show admin authentication modal first
    } else {
        showAlert('Please select a product to delete.', 'danger'); // Alert for no selection
        return;
    }
});

// Admin authentication confirmation button listener
document.getElementById('authConfirmBtn').addEventListener('click', function () {
    const adminUsername = document.getElementById('username').value;
    const adminPassword = document.getElementById('adminPassword').value;

    // Hide any previous error message
    const authError = document.getElementById('authError');
    authError.style.display = 'none';

    // Check if username and password are entered
    if (!adminUsername || !adminPassword) {
        authError.style.display = 'block';
        authError.textContent = 'Please enter both username and password.';
        return;
    }

    // Send credentials to the PHP script for verification
    fetch('authentication.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            username: adminUsername,
            password: adminPassword
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response from server:', data); // Log the server response
        if (data.success) {
            // If authentication is successful, hide the admin authentication modal
            $('#adminAuthModal').modal('hide');

            // Clear the authentication input fields
            document.getElementById('username').value = '';
            document.getElementById('adminPassword').value = '';

            // Show the delete confirmation modal after successful authentication
            $('#deleteConfirmationModal').modal('show');
        } else {
            // Show error message if authentication fails
            authError.style.display = 'block';
            authError.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error during fetch:', error);
        authError.style.display = 'block';
        authError.textContent = 'An error occurred. Please try again.';
    });
});

// Confirm delete after authentication
document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (selectedRow) {
        selectedRow.remove(); // Remove row
        updateTotals(); // Update totals
        $('#deleteConfirmationModal').modal('hide');
        showAlert('Product deleted successfully!'); // Alert for successful deletion
    }
});

    function validatePayment() {
        const payment = parseFloat(paymentInput.value.trim());
        const grandTotal = parseFloat(document.getElementById("grandTotal").textContent.replace(/[^\d.-]/g, ''));
    
        if (!payment || payment <= 0 || isNaN(payment)) {
            showAlert('Please enter a valid payment amount.', 'danger');
            return false;
        } else if (payment < grandTotal) {
            showAlert("Payment is insufficient. Please enter a sufficient amount.", 'danger');
            return false;
        }
        return true;
    }
    
    savebtn.addEventListener('click', function () {
        if (productListBody.rows.length === 0) {
            showAlert('No products to save.', 'danger');
        } else if (validatePayment()) {
            $('#saveSaleConfirmationModal').modal('show');
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

        if (selectedRow) {
            let newRow;
            
            if (event.key === 'ArrowDown') {
                // Move to the next row
                newRow = selectedRow.nextElementSibling;
                if (newRow) toggleRowHighlight(newRow);
            } else if (event.key === 'ArrowUp') {
                // Move to the previous row
                newRow = selectedRow.previousElementSibling;
                if (newRow) toggleRowHighlight(newRow);
            }
        } else {
            // If no row is selected, select the first row on ArrowDown
            if (event.key === 'ArrowDown') {
                const firstRow = productListBody.querySelector('tr');
                if (firstRow) toggleRowHighlight(firstRow);
            }
        }
    });  
    
    // Get the logout button that triggers the modal (assuming it has an ID "logoutButton")
document.getElementById("logout").addEventListener("click", function() {
    // Show the logout confirmation modal
    $("#logoutConfirmationModal").modal("show");
});

// Handle the confirm logout button in the modal
document.getElementById("confirmLogoutBtn").addEventListener("click", function() {
    // Hide the modal
    $("#logoutConfirmationModal").modal("hide");

    window.location.href = "login.php"; 
});

});
