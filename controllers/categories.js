document.addEventListener("DOMContentLoaded", function () {
  // Show alert function with fade-in and fade-out effect
  function showAlert(message, type = "success") {
    const alertBox = document.createElement("div");
    alertBox.className = `alert alert-${type} custom-alert`;
    alertBox.innerText = message;
    document.body.appendChild(alertBox);

    // Add a fade-in effect
    alertBox.style.opacity = "0";
    setTimeout(() => {
      alertBox.style.opacity = "1";
    }, 0); // Delay to allow the element to be added to the DOM

    // Remove alert after 3 seconds
    setTimeout(() => {
      alertBox.style.opacity = "0"; // Fade out effect
      setTimeout(() => {
        document.body.removeChild(alertBox);
      }, 300); // Wait for fade-out to complete
    }, 3000); // Remove after 3 seconds
  }

  // Function to open the edit modal with pre-filled data
  function openEditModal(category_id) {
    fetch(`../models/get_category.php?category_id=${category_id}`)
      .then((response) => response.json())
      .then((data) => {
        document.getElementById("edit_category_id").value = data.category_id;
        document.getElementById("edit_category_name").value =
          data.category_name;

        const editModal = new bootstrap.Modal(
          document.getElementById("editModal")
        );
        editModal.show();
      })
      .catch((error) => console.error("Error:", error));
  }

  // Event delegation for edit and delete buttons
  document.body.addEventListener("click", function (event) {
    if (event.target.classList.contains("editBtn")) {
      const categoryId = event.target.getAttribute("data-id");
      openEditModal(categoryId); // Open the edit modal
    }

    if (event.target.classList.contains("deleteBtn")) {
      const categoryId = event.target.getAttribute("data-id");
      document.getElementById("delete_category_id").value = categoryId;

      const deleteModal = new bootstrap.Modal(
        document.getElementById("deleteModal")
      );
      deleteModal.show(); // Show the delete confirmation modal
    }
    document
      .getElementById("editCategoryForm")
      .addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this);
        fetch("../models/update_category.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            const editModalEl = document.getElementById("editModal");
            const editModal = bootstrap.Modal.getInstance(editModalEl);

            if (data.success) {
              editModal.hide();
              editModalEl.addEventListener(
                "hidden.bs.modal",
                function () {
                  showAlert("Category updated successfully.", "success");
                  setTimeout(() => location.reload(), 2000); // Reload after success
                },
                { once: true }
              );
            } else {
              showAlert(
                "Error occurred while updating the category.",
                "danger"
              );
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            showAlert("An unexpected error occurred.", "danger");
          });
      });

    // Confirm delete button listener
    document
      .getElementById("confirmDeleteBtn")
      .addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default button action (form submission)

        const categoryId = document.getElementById("delete_category_id").value;

        // Ensure categoryId is valid before making the fetch request
        if (!categoryId || isNaN(categoryId)) {
          showAlert("Invalid category ID.", "danger");
          return;
        }

        fetch(`../models/delete_category.php?category_id=${categoryId}`, {
          method: "GET",
        }) // Use category_id
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              const deleteModalEl = document.getElementById("deleteModal");
              const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);

              deleteModal.hide();
              deleteModalEl.addEventListener(
                "hidden.bs.modal",
                function () {
                  showAlert("Category deleted successfully.", "success");
                  setTimeout(() => location.reload(), 2000);
                },
                { once: true }
              );
            } else {
              showAlert("Error deleting category. ", "danger");
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            showAlert("An unexpected error occurred.", "danger");
          });
      });
  });
});
