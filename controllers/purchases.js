document.addEventListener("DOMContentLoaded", function () {
  // Function to show alerts
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

  // Function to open the edit modal
  function openEditModal(button) {
    const purchaseId = button.getAttribute("data-id");
    const row = button.closest("tr");

    // Fetch purchase details to fill the edit form
    document.addEventListener("DOMContentLoaded", function () {
      const productInput = document.getElementById("product_id");
      const productDatalist = document.getElementById("products");

      // Function to fetch product suggestions
      function fetchProductSuggestions(query) {
        fetch(
          "../models/get_suggestion.php?type=products&q=" +
            encodeURIComponent(query)
        )
          .then((response) => {
            if (!response.ok) {
              throw new Error("Network response was not ok");
            }
            return response.json();
          })
          .then((data) => {
            // Clear previous options
            productDatalist.innerHTML = "";

            // Populate datalist with new options
            data.forEach((product) => {
              const option = document.createElement("option");
              option.value = product.id; // Use ID as the value
              option.textContent = product.text; // Use name as display text
              productDatalist.appendChild(option);
            });
          })
          .catch((error) => {
            console.error("Error fetching suggestions:", error);
            productDatalist.innerHTML = ""; // Clear suggestions if there's an error
          });
      }

      // Event listener for input
      productInput.addEventListener("input", function () {
        const query = productInput.value;
        if (query.length > 0) {
          fetchProductSuggestions(query);
        } else {
          productDatalist.innerHTML = ""; // Clear suggestions if input is empty
          // Optional: Clear other relevant fields if needed
        }
      });
    });
    const productId = row.getAttribute("data-product-id");
    const supplierId = row.getAttribute("data-supplier-id");
    const date = row.children[3].textContent;
    const purchaseQuantity = row.children[4].textContent;
    const purchaseAmount = row.children[5].textContent
      .replace("₱", "")
      .replace(",", "");

    // Fill the edit modal with purchase details
    document.getElementById("update_purchase_id").value = purchaseId; // Updated to match modal structure
    document.getElementById("edit_product_id").value = productId;
    document.getElementById("edit_supplier_id").value = supplierId;
    document.getElementById("edit_date").value = date;
    document.getElementById("edit_purchase_quantity").value = purchaseQuantity;
    document.getElementById("edit_purchase_amount").value = purchaseAmount;

    // Show the edit modal
    const editModal = new bootstrap.Modal(document.getElementById("editModal"));
    editModal.show();
  }

  // Edit Purchase
  document.querySelectorAll(".editBtn").forEach((button) => {
    button.addEventListener("click", function () {
      openEditModal(this);
    });
  });

  // Handle form submission for editing purchase via AJAX
  document
    .getElementById("editForm")
    .addEventListener("submit", function (event) {
      event.preventDefault(); // Prevent form submission from reloading the page

      const formData = new FormData(this);
      fetch("../models/update_purchase.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          const editModalEl = document.getElementById("editModal");
          const editModal = bootstrap.Modal.getInstance(editModalEl);

          if (data.success) {
            // Hide the modal first
            editModal.hide();

            // Show alert after the modal is fully hidden
            editModalEl.addEventListener(
              "hidden.bs.modal",
              function () {
                showAlert("Purchase row updated successfully.", "success"); // Show success message using custom alert
                // Optionally, refresh the page to reflect changes after a slight delay
                setTimeout(() => location.reload(), 2000);
              },
              { once: true }
            );
          } else {
            showAlert("Error occured will updating the purchase.", "danger"); // Show error message using custom alert
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showAlert("An unexpected error occurred.", "danger");
        });
    });

  // Event listener for delete buttons in the actions column
  document.querySelectorAll(".deleteBtn").forEach((button) => {
    button.addEventListener("click", function () {
      const purchaseId = this.getAttribute("data-id");
      // Populate the delete product modal with the purchase ID
      document.getElementById("delete_purchase_id").value = purchaseId;

      // Show the delete modal
      const deleteModal = new bootstrap.Modal(
        document.getElementById("deleteModal")
      );
      deleteModal.show();
    });
  });

  // Confirm delete button listener
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener("click", function (event) {
      event.preventDefault(); // Prevent default form submission

      // Create FormData object
      const formData = new FormData();
      const purchaseId = document.getElementById("delete_purchase_id").value;
      formData.append("purchase_id", purchaseId);

      // AJAX request to delete the purchase
      fetch("../models/delete_purchase.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Find and remove the table row
            const row = document.querySelector(
              `tr[data-purchase-id="${purchaseId}"]`
            );
            if (row) {
              row.remove();
            }

            // Hide the modal immediately
            const deleteModalEl = document.getElementById("deleteModal");
            const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
            deleteModal.hide();

            // Show success alert after modal is hidden
            deleteModalEl.addEventListener(
              "hidden.bs.modal",
              function () {
                showAlert("Purchase deleted successfully.", "success");
              },
              { once: true }
            );
          } else {
            showAlert("Error deleting purchase. ", "danger");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showAlert("An unexpected error occurred.", "danger");
        });
    });
  }

  const addModal = document.getElementById("addModal");

  addModal.addEventListener("show.bs.modal", function () {
    // Reset the product dropdown to the placeholder
    const productDropdown = document.getElementById("product_id");
    productDropdown.value = ""; // Sets to the placeholder "Select Product"

    // Reset the supplier dropdown to the placeholder
    const supplierDropdown = document.getElementById("supplier_id");
    supplierDropdown.value = ""; // Sets to the placeholder "Select Supplier"
  });
});
