document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".editBtn");

    // Function to show alerts
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

    // Edit Product
    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            const productId = this.getAttribute("data-id");
            const row = this.closest("tr");

            // Fetch product details to fill the edit form
            const productName = row.children[1].textContent;
            const barcode = row.children[2].textContent;
            const category_id = row.children[3].textContent;
            const buyingPrice = row.children[4].textContent.replace('₱', '').replace(',', '');
            const sellingPrice = row.children[5].textContent.replace('₱', '').replace(',', '');
            const stocks = row.children[6].textContent;
            const threshold = row.children[7].textContent;

            // Fill the edit modal with product details
            document.getElementById("edit_product_product_id").value = productId;
            document.getElementById("edit_product_name").value = productName;
            document.getElementById("edit_barcode").value = barcode;
            document.getElementById("edit_category_id").value = category_id;
            document.getElementById("edit_buying_price").value = buyingPrice;
            document.getElementById("edit_selling_price").value = sellingPrice;
            document.getElementById("edit_stocks").value = stocks;
            document.getElementById("edit_threshold").value = threshold;

            // Show the edit modal
            const editModal = new bootstrap.Modal(document.getElementById("editModal"));
            editModal.show();
        });
    });

    // Handle form submission for editing product via AJAX
    document.getElementById('editForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission from reloading the page

        const formData = new FormData(this);
        fetch('update_product.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const editModalEl = document.getElementById('editModal');
            const editModal = bootstrap.Modal.getInstance(editModalEl);

            if (data.success) {
                // Hide the modal first
                editModal.hide();

                // Wait for the modal to fully hide before showing the alert
                editModalEl.addEventListener('hidden.bs.modal', function () {
                    showAlert(data.message, 'success'); // Show success message using custom alert
                    // Optionally refresh the page to reflect changes
                    setTimeout(() => {
                        location.reload(); // Refresh page to reflect changes
                    }, 1000); // Delay page reload for smoother UX
                }, { once: true });
            } else {
                showAlert(data.message, 'danger'); // Show error message using custom alert
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An unexpected error occurred.', 'danger');
        });
    });

    // Event listener for delete buttons in the actions column
    document.querySelectorAll('.deleteBtn').forEach(function (button) {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-id');
            // Populate the delete product modal with the product ID
            document.getElementById('delete_product_product_id').value = productId;

            // Show the delete modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });

    // Confirm delete button listener
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function (event) {
            event.preventDefault();  // Prevent page redirect

            // Create FormData object
            const formData = new FormData();
            const productId = document.getElementById('delete_product_product_id').value;
            formData.append('product_id', productId);

            // AJAX request to delete the product
            fetch('delete_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Product deleted successfully');

                    // Hide the modal first
                    const deleteModalEl = document.getElementById('deleteModal');
                    const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                    deleteModal.hide();

                    // Wait for the modal to fully hide before removing the row and showing the alert
                    deleteModalEl.addEventListener('hidden.bs.modal', function () {
                        // Remove the row after the modal hides
                        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                        if (row) {
                            row.remove();
                        }

                        // Show success alert after the row is removed
                        showAlert('Product deleted successfully.', 'success');
                    }, { once: true });
                } else {
                    showAlert('Error deleting product: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An unexpected error occurred.', 'danger');
            });
        });
    }

    // Event listener for barcode input
    const productBarcodeInput = document.getElementById('productBarcodeInput');
    if (productBarcodeInput) {
        productBarcodeInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission if inside a form

                var barcode = productBarcodeInput.value.trim();

                if (barcode) {
                    console.log('Barcode scanned: ' + barcode);
                }
            }
        });
    }
});
