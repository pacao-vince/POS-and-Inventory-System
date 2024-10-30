document.addEventListener("DOMContentLoaded", function () {
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

    // Event delegation for Edit and Delete buttons
    document.addEventListener('click', function (event) {
        // Handle Edit button click
        if (event.target.classList.contains('editBtn')) {
            openEditModal(event.target);
        }

        // Handle Delete button click
        if (event.target.classList.contains('deleteBtn')) {
            const supplierId = event.target.getAttribute('data-id');
            document.getElementById('delete_supplier_id').value = supplierId;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    });

    // Function to open the edit modal
    function openEditModal(button) {
        const supplierId = button.getAttribute("data-id");
        const row = button.closest("tr");
        const name = row.children[1].textContent;
        const address = row.children[2].textContent;
        const contact_num = row.children[3].textContent;

        // Fill the edit modal with supplier details
        document.getElementById("edit_supplier_id").value = supplierId; 
        document.getElementById("edit_name").value = name;
        document.getElementById("edit_address").value = address;
        document.getElementById("edit_contact_num").value = contact_num;

        // Show the edit modal
        const editModal = new bootstrap.Modal(document.getElementById("editModal"));
        editModal.show();
    }

    // Handle form submission for editing supplier via AJAX
    document.getElementById('editForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission from reloading the page

        const formData = new FormData(this);
        fetch('update_supplier.php', {
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

                // Show alert after the modal is fully hidden
                editModalEl.addEventListener('hidden.bs.modal', function () {
                    showAlert('Supplier updated successfully.', 'success'); // Show success message using custom alert
                    // Optionally, refresh the page to reflect changes after a slight delay
                    setTimeout(() => location.reload(), 2000);
                }, { once: true });
            } else {
                showAlert('Error occurred while updating the supplier.', 'danger'); // Show error message using custom alert
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An unexpected error occurred.', 'danger');
        });
    });

    // Confirm delete button listener
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function (event) {
            event.preventDefault();  // Prevent default form submission

            // Create FormData object for deleting
            const formData = new FormData();
            const supplierId = document.getElementById('delete_supplier_id').value;
            formData.append('supplier_id', supplierId);

            // AJAX request to delete the supplier
            fetch('delete_supplier.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Find and remove the table row
                    const row = document.querySelector(`tr[data-supplier-id="${supplierId}"]`);
                    if (row) {
                        row.remove();
                    }

                    // Hide the modal immediately
                    const deleteModalEl = document.getElementById('deleteModal');
                    const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
                    deleteModal.hide();

                    // Show success alert after modal is hidden
                    deleteModalEl.addEventListener('hidden.bs.modal', function () {
                        showAlert('Supplier deleted successfully.', 'success');
                    }, { once: true });
                } else {
                    showAlert('Error deleting supplier.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An unexpected error occurred.', 'danger');
            });
        });
    }
});
