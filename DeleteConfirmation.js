document.addEventListener('DOMContentLoaded', function() {
    const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const closeDeleteModal = document.querySelector('.delete-close-modal');
    let selectedRow = null;

    // Function to open the delete confirmation modal
    function openDeleteConfirmation(row) {
        selectedRow = row;
        deleteConfirmationModal.style.display = 'flex';
    }

    // Event listener for confirming deletion
    confirmDeleteBtn.addEventListener('click', function() {
        if (selectedRow) {
            selectedRow.remove();  // Remove the selected row
            if (window.updateTotals) {
                window.updateTotals();  // Update totals after deletion
            }
            deleteConfirmationModal.style.display = 'none';
            selectedRow = null;  // Reset the selected row
        }
    });

    // Event listener for canceling deletion
    cancelDeleteBtn.addEventListener('click', function() {
        deleteConfirmationModal.style.display = 'none';
    });

    // Event listener for closing the delete modal
    closeDeleteModal.addEventListener('click', function() {
        deleteConfirmationModal.style.display = 'none';
    });

    // Export function to be used in other scripts if needed
    window.openDeleteConfirmation = openDeleteConfirmation;
});
