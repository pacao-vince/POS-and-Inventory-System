document.addEventListener("DOMContentLoaded", function () {
    // Function to show alerts
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

    // Function to open the edit modal with pre-filled data
    function openEditModal(category_id) {
        fetch(`get_category.php?category_id=${category_id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_category_id').value = data.category_id;
                document.getElementById('edit_category_name').value = data.category_name;

                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            })
            .catch(error => console.error('Error:', error));
    }

    // Event delegation for edit and delete buttons
    document.body.addEventListener('click', function (event) {
        if (event.target.classList.contains('editBtn')) {
            const categoryId = event.target.getAttribute('data-id');
            openEditModal(categoryId); // Open the edit modal
        }

        if (event.target.classList.contains('deleteBtn')) {
            const categoryId = event.target.getAttribute('data-id');
            document.getElementById('delete_category_id').value = categoryId;

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show(); // Show the delete confirmation modal
        }
    });

    // Handle form submission for editing  via AJAX
    document.getElementById("editForm").addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        fetch('update_category.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                const editModalEl = document.getElementById('editModal');
                const editModal = bootstrap.Modal.getInstance(editModalEl);

                if (data.success) {
                    editModal.hide();
                    editModalEl.addEventListener('hidden.bs.modal', function () {
                        showAlert('success', 'Category updated successfully.');
                        setTimeout(() => location.reload(), 2000);
                    }, { once: true });
                } else {
                    showAlert('danger', 'Error updating category: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An unexpected error occurred.');
            });
    });

    // Confirm delete button listener
    document.getElementById('confirmDeleteBtn').addEventListener('click', function (event) {
        event.preventDefault(); // Prevent default button action (form submission)

        const categoryId = document.getElementById('delete_category_id').value;

        // Ensure categoryId is valid before making the fetch request
        if (!categoryId || isNaN(categoryId)) {
            showAlert('danger', 'Invalid category ID.');
            return;
        }

        fetch(`delete_category.php?category_id=${categoryId}`, { method: 'GET' }) // Use category_id
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const deleteModalEl = document.getElementById('deleteModal');
                    const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);

                    deleteModal.hide();
                    deleteModalEl.addEventListener('hidden.bs.modal', function () {
                        showAlert('success', 'Category deleted successfully.');
                        setTimeout(() => location.reload(), 2000);
                    }, { once: true });
                } else {
                    showAlert('danger', 'Error deleting category: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An unexpected error occurred.');
            });
    });



});
