document.addEventListener("DOMContentLoaded", function () {
	const editButtons = document.querySelectorAll(".editBtn");
	const searchInput = document.getElementById("searchInput");
	const categoryDropdown = document.getElementById("categoryDropdown");
	const categoryFilterItems = document.querySelectorAll(
		"#categoryFilter .dropdown-item"
	);
	const productsTable = document.getElementById("products");
	const tableRows = productsTable
		.getElementsByTagName("tbody")[0]
		.getElementsByTagName("tr");

	// Function to filter products based on search and category
	function filterProducts() {
		const searchTerm = searchInput.value.toLowerCase();
		const selectedCategory =
			categoryDropdown.getAttribute("data-selected-category") || "";

		Array.from(tableRows).forEach((row) => {
			const productName = row
				.querySelector(".product-name")
				.textContent.toLowerCase();
			const category = row.cells[3].textContent.toLowerCase();

			const matchesSearch = productName.includes(searchTerm);
			const matchesCategory =
				!selectedCategory || category === selectedCategory;

			row.style.display = matchesSearch && matchesCategory ? "" : "none";
		});
	}

	// Event Listener for Search Input
	searchInput.addEventListener("input", filterProducts);

	// Event Listener for Category Dropdown
	categoryFilterItems.forEach((item) => {
		item.addEventListener("click", function (event) {
			event.preventDefault(); // Prevent default link behavior
			const selectedCategory = item
				.getAttribute("data-value")
				.toLowerCase();
			const selectedCategoryText = item.textContent;

			// Update button text and data attribute
			categoryDropdown.querySelector("span.me-1").textContent =
				selectedCategoryText;
			categoryDropdown.setAttribute(
				"data-selected-category",
				selectedCategory
			);

			// Filter products based on the new selection
			filterProducts();
		});
	});

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

	// Check for alert message in sessionStorage
	const alertMessage = sessionStorage.getItem("alertMessage");
	const alertType = sessionStorage.getItem("alertType");

	if (alertMessage) {
		// Delay the appearance of the alert
		setTimeout(() => {
			showAlert(alertMessage, alertType);
		}, 250);
		// Clear the message from sessionStorage
		sessionStorage.removeItem("alertMessage");
		sessionStorage.removeItem("alertType");
	}

	// Add form submission event listener
	document
		.getElementById("addForm")
		.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent default form submission

			const formData = new FormData(this); // Capture form data

			// Make a POST request
			fetch("../models/create_product.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					// Reset the product dropdown to the placeholder
					const categorySelect =
						document.getElementById("category_id");
					categorySelect.value = ""; // Sets to the placeholder "Select Product"

					const addModalEl = document.getElementById("addModal");
					const addModal = bootstrap.Modal.getInstance(addModalEl);

					// Hide the modal
					addModal.hide();

					// Add a one-time listener for when the modal is fully hidden
					addModalEl.addEventListener(
						"hidden.bs.modal",
						function () {
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

	// Edit Product
	editButtons.forEach((button) => {
		button.addEventListener("click", function () {
			const productId = this.getAttribute("data-id");
			const row = this.closest("tr");

			console.log(editButtons); // Check if this logs the button elements

			// Get data attributes from the selected row
			const categoryId = row.getAttribute("data-category-id");

			// Fetch product details to fill the edit form
			const productName = row.children[1].textContent;
			const barcode = row.children[2].textContent;

			const buyingPrice = row.children[4].textContent
				.replace("₱", "")
				.replace(",", "");
			const sellingPrice = row.children[5].textContent
				.replace("₱", "")
				.replace(",", "");
			const stocks = row.children[6].textContent;
			const threshold = row.children[7].textContent;

			// Fill the edit modal with product details
			document.getElementById("edit_product_product_id").value =
				productId;
			document.getElementById("edit_product_name").value = productName;
			document.getElementById("edit_barcode").value = barcode;
			document.getElementById("edit_category_id").value = categoryId;
			document.getElementById("edit_buying_price").value = buyingPrice;
			document.getElementById("edit_selling_price").value = sellingPrice;
			document.getElementById("edit_stocks").value = stocks;
			document.getElementById("edit_threshold").value = threshold;

			// Show the edit modal
			const editModal = new bootstrap.Modal(
				document.getElementById("editModal")
			);
			editModal.show();
		});
	});

	// Handle form submission for editing product via AJAX
	document
		.getElementById("editForm")
		.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent form submission from reloading the page

			const formData = new FormData(this);
			fetch("../models/update_product.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					const editModalEl = document.getElementById("editModal");
					const editModal = bootstrap.Modal.getInstance(editModalEl);

					// Hide the modal first
					editModal.hide();

					// Wait for the modal to fully hide before refreshing the page
					editModalEl.addEventListener(
						"hidden.bs.modal",
						function () {
							// Store the alert message in sessionStorage
							sessionStorage.setItem(
								"alertMessage",
								data.message
							);
							sessionStorage.setItem(
								"alertType",
								data.success ? "success" : "danger"
							);

							// Refresh the page
							setTimeout(() => location.reload(), 100); // 100ms delay before reload
						},
						{ once: true }
					);
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
	document.querySelectorAll(".deleteBtn").forEach(function (button) {
		button.addEventListener("click", function () {
			const productId = this.getAttribute("data-id");
			// Populate the delete product modal with the product ID
			document.getElementById("delete_product_product_id").value =
				productId;

			// Show the delete modal
			const deleteModal = new bootstrap.Modal(
				document.getElementById("deleteModal")
			);
			deleteModal.show();
		});
	});

	// Confirm delete button listener
	const deleteBtn = document.getElementById("confirmDeleteBtn");
	if (deleteBtn) {
		deleteBtn.addEventListener("click", function (event) {
			event.preventDefault(); // Prevent page redirect

			// Create FormData object
			const formData = new FormData();
			const productId = document.getElementById(
				"delete_product_product_id"
			).value;
			formData.append("product_id", productId);

			// AJAX request to delete the product
			fetch("../models/delete_product.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					// Hide the modal first
					const deleteModalEl =
						document.getElementById("deleteModal");
					const deleteModal =
						bootstrap.Modal.getInstance(deleteModalEl);
					deleteModal.hide();

					// Wait for the modal to fully hide before refreshing the page
					deleteModalEl.addEventListener(
						"hidden.bs.modal",
						function () {
							// Store the alert message in sessionStorage
							sessionStorage.setItem(
								"alertMessage",
								data.message
							);
							sessionStorage.setItem(
								"alertType",
								data.success ? "success" : "danger"
							);

							// Refresh the page
							setTimeout(() => location.reload(), 0); // 100ms delay before reload
						},
						{ once: true }
					);
				})
				.catch((error) => {
					console.error("Error:", error);
					showAlert("An unexpected error occurred.", "danger");
				});
		});
	}

	// Event listener for barcode input
	const productBarcodeInput = document.getElementById("productBarcodeInput");
	if (productBarcodeInput) {
		productBarcodeInput.addEventListener("keypress", function (event) {
			if (event.key === "Enter") {
				event.preventDefault(); // Prevent form submission if inside a form

				var barcode = productBarcodeInput.value.trim();

				if (barcode) {
					console.log("Barcode scanned: " + barcode);
				}
			}
		});
	}
});
