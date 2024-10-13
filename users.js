document.addEventListener('DOMContentLoaded', function () {
    // Edit button click event listener
    document.querySelectorAll('.editBtn').forEach(function (button) {
        button.addEventListener('click', function () {
            const userId = this.getAttribute('data-id');
            const username = this.closest('tr').querySelector('td:nth-child(2)').textContent;
            const email = this.closest('tr').querySelector('td:nth-child(3)').textContent;
            const userType = this.closest('tr').querySelector('td:nth-child(4)').textContent;

            // Set values in the modal
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_user_type').value = userType.toLowerCase();

            // Show the modal
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        });
    });

    // Handle form submission for edit user via AJAX
    document.getElementById('editForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission from reloading the page

        const formData = new FormData(this);
        fetch('update_user.php', {
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
                        showAlert('success', data.message); // Show success message using custom alert
                    }, { once: true });

                    // Optionally, refresh the page to reflect changes after a slight delay
                    setTimeout(() => {
                        location.reload(); // Refresh page to reflect changes
                    }, 2000);
                } else {
                    showAlert('danger', data.message); // Show error message using custom alert
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An unexpected error occurred.');
            });
    });

    // Event listener for delete buttons in the actions column
    document.querySelectorAll('.deleteBtn').forEach(function (button) {
        button.addEventListener('click', function () {
            const userId = this.getAttribute('data-id');
            // Populate the delete user modal with the user ID
            document.getElementById('delete_user_id').value = userId;

            // Show the delete modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });

    // Confirm delete button listener
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            // Create FormData object
            const formData = new FormData();
            const userId = document.getElementById('delete_user_id').value;
            formData.append('user_id', userId);

            // AJAX request to delete the user
            fetch('delete_user.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (row) {
                            row.remove();

                            const deleteModalEl = document.getElementById('deleteModal');
                            const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);

                            // Hide the modal first
                            deleteModal.hide();

                            // Show alert after the modal is fully hidden
                            deleteModalEl.addEventListener('hidden.bs.modal', function () {
                                showAlert('success', 'User deleted successfully.');
                            }, { once: true });
                        }
                    } else {
                        showAlert('danger', 'Error deleting user: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An unexpected error occurred.');
                });
        });
    }

    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
    
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="margin: 0 auto; text-align: center;">
                ${message}
            </div>
        `;
    
        alertContainer.innerHTML = alertHtml;

        // Automatically hide the alert after 3 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                alert.classList.add('hide');
                alert.addEventListener('transitionend', () => alert.remove());
            }
        }, 3000);
    }
});
