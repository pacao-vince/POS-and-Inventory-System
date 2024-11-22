document.addEventListener("DOMContentLoaded", function () {
	// Fetch purchase details to fill the edit form

	// 	const productInput = document.getElementById("product_id");
	// 	const productDatalist = document.getElementById("products");

	// // Function to fetch product suggestions
	// function fetchProductSuggestions(query) {
	// 	fetch(
	// 		"../models/get_suggestion.php?type=products&q=" +
	// 			encodeURIComponent(query)
	// 	)
	// 		.then((response) => {
	// 			if (!response.ok) {
	// 				throw new Error("Network response was not ok");
	// 			}
	// 			return response.json();
	// 		})
	// 		.then((data) => {
	// 			// Clear previous options
	// 			productDatalist.innerHTML = "";

	// 			// Populate datalist with new options
	// 			data.forEach((product) => {
	// 				const option = document.createElement("option");
	// 				option.value = product.id; // Use ID as the value
	// 				option.textContent = product.text; // Use name as display text
	// 				productDatalist.appendChild(option);
	// 			});
	// 		})
	// 		.catch((error) => {
	// 			console.error("Error fetching suggestions:", error);
	// 			productDatalist.innerHTML = ""; // Clear suggestions if there's an error
	// 		});
	// }

	// // Event listener for input
	// productInput.addEventListener("input", function () {
	// 	const query = productInput.value;
	// 	if (query.length > 0) {
	// 		fetchProductSuggestions(query);
	// 	} else {
	// 		productDatalist.innerHTML = ""; // Clear suggestions if input is empty
	// 		// Optional: Clear other relevant fields if needed
	// 	}
	// });

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

	// Check if there's a message in sessionStorage and show it
	function checkAlertFromSession() {
		const message = sessionStorage.getItem("alertMessage");
		const type = sessionStorage.getItem("alertType");

		if (message) {
			// Add 250ms delay before showing the alert
			setTimeout(() => {
				showAlert(message, type);
				sessionStorage.removeItem("alertMessage");
				sessionStorage.removeItem("alertType");
			}, 250); // Delay the alert display by 250ms
		}
	}

	// Add form submission event listener
	document
		.getElementById("addForm")
		.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent default form submission

			const formData = new FormData(this); // Capture form data

			// Make a POST request
			fetch("../models/create_purchase.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					for (let [key, value] of formData.entries()) {
						console.log(`${key}: ${value}`);
					}

					const addModalEl = document.getElementById("addModal");
					const addModal = bootstrap.Modal.getInstance(addModalEl);

					// Hide the modal
					addModal.hide();

					// Add a one-time listener for when the modal is fully hidden
					addModalEl.addEventListener(
						"hidden.bs.modal",
						function () {
							// Reset the product dropdown to the placeholder
							const productDropdown =
								document.getElementById("product_id");
							productDropdown.value = ""; // Sets to the placeholder "Select Product"

							// Reset the supplier dropdown to the placeholder
							const supplierDropdown =
								document.getElementById("supplier_id");
							supplierDropdown.value = ""; // Sets to the placeholder "Select Supplier"

							// Store the alert message in sessionStorage
							sessionStorage.setItem(
								"alertMessage",
								data.message
							);
							sessionStorage.setItem(
								"alertType",
								data.success ? "success" : "danger"
							);

							// Reload the page to reflect changes
							location.reload(); //
						},
						{ once: true }
					);
				})
				.catch(() => {
					// Store a generic error message in sessionStorage
					sessionStorage.setItem(
						"alertMessage",
						"An error occurred. Please try again."
					);
					sessionStorage.setItem("alertType", "danger");

					// Reload the page to show the error alert
					location.reload(); // Uncomment if needed
				});
		});

	// Function to open the edit modal
	function openEditModal(button) {
		const purchaseId = button.getAttribute("data-id");
		const row = button.closest("tr");

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
		document.getElementById("edit_purchase_quantity").value =
			purchaseQuantity;
		document.getElementById("edit_purchase_amount").value = purchaseAmount;

		// Show the edit modal
		const editModal = new bootstrap.Modal(
			document.getElementById("editModal")
		);
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
			event.preventDefault(); // Prevent the default form submission

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
						editModal.hide();
						// Reload the page immediately after successful edit
						setTimeout(() => location.reload(), 100); // 100ms delay before reload
						// Store the success message in sessionStorage
						sessionStorage.setItem(
							"alertMessage",
							"Purchase updated successfully."
						);
						sessionStorage.setItem("alertType", "success");
					} else {
						sessionStorage.setItem(
							"alertMessage",
							"Error occurred while updating the purchase."
						);
						sessionStorage.setItem("alertType", "danger");
					}
				})
				.catch((error) => {
					console.error("Error:", error);
					sessionStorage.setItem(
						"alertMessage",
						"An unexpected error occurred."
					);
					sessionStorage.setItem("alertType", "danger");
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
			const purchaseId =
				document.getElementById("delete_purchase_id").value;
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
						const deleteModalEl =
							document.getElementById("deleteModal");
						const deleteModal =
							bootstrap.Modal.getInstance(deleteModalEl);
						deleteModal.hide();

						// Show success alert after modal is hidden
						deleteModalEl.addEventListener(
							"hidden.bs.modal",
							function () {
								showAlert(
									"Purchase deleted successfully.",
									"success"
								);
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

	checkAlertFromSession(); // Check and show alert if any message exists
});
