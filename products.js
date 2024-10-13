document.addEventListener("DOMContentLoaded", function () {
	// Add event listeners for edit and delete buttons
	const editButtons = document.querySelectorAll(".editBtn")
	const searchInput = document.getElementById("searchInput")
	const categoryDropdown = document.getElementById("categoryDropdown")
	const categoryFilterItems = document.querySelectorAll(
		"#categoryFilter .dropdown-item"
	)
	const productsTable = document.getElementById("productsTable")
	const tableRows = productsTable
		.getElementsByTagName("tbody")[0]
		.getElementsByTagName("tr")

	// Function to filter products based on search and category
	function filterProducts() {
		const searchTerm = searchInput.value.toLowerCase()
		const selectedCategory =
			categoryDropdown.getAttribute("data-selected-category") || ""

		Array.from(tableRows).forEach((row) => {
			const productName = row
				.querySelector(".product-name")
				.textContent.toLowerCase()
			const category = row.cells[3].textContent.toLowerCase()

			const matchesSearch = productName.includes(searchTerm)
			const matchesCategory =
				!selectedCategory || category === selectedCategory

			row.style.display = matchesSearch && matchesCategory ? "" : "none"
		})
	}

	// Event Listener for Search Input
	searchInput.addEventListener("input", filterProducts)

	// Event Listener for Category Dropdown
	categoryFilterItems.forEach((item) => {
		item.addEventListener("click", function (event) {
			event.preventDefault() // Prevent default link behavior
			const selectedCategory = item
				.getAttribute("data-value")
				.toLowerCase()
			const selectedCategoryText = item.textContent

			// Update button text and data attribute
			categoryDropdown.querySelector("span.me-1").textContent =
				selectedCategoryText
			categoryDropdown.setAttribute(
				"data-selected-category",
				selectedCategory
			)

			// Filter products based on the new selection
			filterProducts()
		})
	})

	// Edit Product
	editButtons.forEach((button) => {
		button.addEventListener("click", function () {
			const productId = this.getAttribute("data-id")
			const row = this.closest("tr")

			// Fetch product details to fill the edit form
			const productName = row.children[1].textContent
			const barcode = row.children[2].textContent
			const categoryId = row.children[3].textContent
			const buyingPrice = row.children[4].textContent
				.replace("₱", "")
				.replace(",", "")
			const sellingPrice = row.children[5].textContent
				.replace("₱", "")
				.replace(",", "")
			const stocks = row.children[6].textContent
			const threshold = row.children[7].textContent

			// Fill the edit modal with product details
			document.getElementById("edit_product_id").value = productId
			document.getElementById("edit_product_name").value = productName
			document.getElementById("edit_barcode").value = barcode
			document.getElementById("edit_category_id").value = categoryId
			document.getElementById("edit_buying_price").value = buyingPrice
			document.getElementById("edit_selling_price").value = sellingPrice
			document.getElementById("edit_stocks").value = stocks
			document.getElementById("edit_threshold").value = threshold

			// Show the edit modal
			const editModal = new bootstrap.Modal(
				document.getElementById("editModal")
			)
			editModal.show()
		})
	})

	// Handle form submission for editing product via AJAX
	document
		.getElementById("editForm")
		.addEventListener("submit", function (event) {
			event.preventDefault() // Prevent form submission from reloading the page

			const formData = new FormData(this)
			fetch("update_product.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					const editModalEl = document.getElementById("editModal")
					const editModal = bootstrap.Modal.getInstance(editModalEl)

					if (data.success) {
						// Hide the modal first
						editModal.hide()

						// Show alert after the modal is fully hidden
						editModalEl.addEventListener(
							"hidden.bs.modal",
							function () {
								showAlert("success", data.message) // Show success message using custom alert
							},
							{ once: true }
						)

						// Optionally, refresh the page to reflect changes after a slight delay
						setTimeout(() => {
							location.reload() // Refresh page to reflect changes
						}, 2000)
					} else {
						showAlert("danger", data.message) // Show error message using custom alert
					}
				})
				.catch((error) => {
					console.error("Error:", error)
					showAlert("danger", "An unexpected error occurred.")
				})
		})

	// Event listener for delete buttons in the actions column
	document.querySelectorAll(".deleteBtn").forEach(function (button) {
		button.addEventListener("click", function () {
			const productId = this.getAttribute("data-id")
			// Populate the delete product modal with the product ID
			document.getElementById("delete_product_id").value = productId

			// Show the delete modal
			const deleteModal = new bootstrap.Modal(
				document.getElementById("deleteModal")
			)
			deleteModal.show()
		})
	})

	// Confirm delete button listener
	const deleteBtn = document.getElementById("confirmDeleteBtn")
	if (deleteBtn) {
		deleteBtn.addEventListener("click", function (event) {
			event.preventDefault() // Prevent page redirect

			// Create FormData object
			const formData = new FormData()
			const productId = document.getElementById("delete_product_id").value
			formData.append("product_id", productId)

			// AJAX request to delete the product
			fetch("delete_product.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					if (data.success) {
						console.log("Product deleted successfully") // Debugging message

						// Find and remove the table row
						const row = document.querySelector(
							`tr[data-product-id="${productId}"]`
						)
						if (row) {
							row.remove()
							console.log("Row removed successfully") // Debugging message
						} else {
							console.log("Row not found") // Debugging message if row isn't found
						}

						// Hide the modal immediately
						const deleteModalEl =
							document.getElementById("deleteModal")
						const deleteModal =
							bootstrap.Modal.getInstance(deleteModalEl)
						deleteModal.hide()

						// Show success alert after modal is hidden
						deleteModalEl.addEventListener(
							"hidden.bs.modal",
							function () {
								showAlert(
									"success",
									"Product deleted successfully."
								)
							},
							{ once: true }
						)
					} else {
						showAlert(
							"danger",
							"Error deleting product: " + data.message
						)
					}
				})
				.catch((error) => {
					console.error("Error:", error)
					showAlert("danger", "An unexpected error occurred.")
				})
		})
	}

	// Function to show alerts
	function showAlert(type, message) {
		const alertContainer = document.getElementById("alert-container")

		const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="margin: 0 auto; text-align: center;">
                ${message}
            </div>
        `

		alertContainer.innerHTML = alertHtml

		// Automatically hide the alert after 3 seconds
		setTimeout(() => {
			const alert = document.querySelector(".alert")
			if (alert) {
				alert.classList.remove("show")
				alert.classList.add("hide")
				alert.addEventListener("transitionend", () => alert.remove())
			}
		}, 3000)
	}

	// Event listener for barcode input
	const productBarcodeInput = document.getElementById("productBarcodeInput")
	if (productBarcodeInput) {
		productBarcodeInput.addEventListener("keypress", function (event) {
			if (event.key === "Enter") {
				event.preventDefault() // Prevent form submission if inside a form

				var barcode = productBarcodeInput.value.trim()

				if (barcode) {
					console.log("Barcode scanned: " + barcode)
				}
			}
		})
	}
})
