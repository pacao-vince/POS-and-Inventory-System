document.addEventListener("DOMContentLoaded", function () {
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

	// Function to open the edit modal
	function openEditModal(button) {
		const purchaseId = button.getAttribute("data-id")
		const row = button.closest("tr")

		// Fetch purchase details to fill the edit form
		document.addEventListener("DOMContentLoaded", function () {
			const productInput = document.getElementById("product_id")
			const productDatalist = document.getElementById("products")

			// Function to fetch product suggestions
			function fetchProductSuggestions(query) {
				fetch(
					"get_suggestion.php?type=products&q=" +
						encodeURIComponent(query)
				)
					.then((response) => {
						if (!response.ok) {
							throw new Error("Network response was not ok")
						}
						return response.json()
					})
					.then((data) => {
						// Clear previous options
						productDatalist.innerHTML = ""

						// Populate datalist with new options
						data.forEach((product) => {
							const option = document.createElement("option")
							option.value = product.id // Use ID as the value
							option.textContent = product.text // Use name as display text
							productDatalist.appendChild(option)
						})
					})
					.catch((error) => {
						console.error("Error fetching suggestions:", error)
						productDatalist.innerHTML = "" // Clear suggestions if there's an error
					})
			}

			// Event listener for input
			productInput.addEventListener("input", function () {
				const query = productInput.value
				if (query.length > 0) {
					fetchProductSuggestions(query)
				} else {
					productDatalist.innerHTML = "" // Clear suggestions if input is empty
					// Optional: Clear other relevant fields if needed
				}
			})
		})
		const productId = row.children[1].textContent
		const supplier = row.children[2].textContent
		const date = row.children[3].textContent
		const purchaseAmount = row.children[4].textContent
			.replace("₱", "")
			.replace(",", "")

		// Fill the edit modal with purchase details
		document.getElementById("update_purchase_id").value = purchaseId // Updated to match modal structure
		document.getElementById("edit_product_id").value = productId
		document.getElementById("edit_supplier").value = supplier
		document.getElementById("edit_date").value = date
		document.getElementById("edit_purchase_amount").value = purchaseAmount

		// Show the edit modal
		const editModal = new bootstrap.Modal(
			document.getElementById("editModal")
		)
		editModal.show()
	}

	// Edit Purchase
	document.querySelectorAll(".editBtn").forEach((button) => {
		button.addEventListener("click", function () {
			openEditModal(this)
		})
	})

	// Handle form submission for editing purchase via AJAX
	document
		.getElementById("editForm")
		.addEventListener("submit", function (event) {
			event.preventDefault() // Prevent form submission from reloading the page

			const formData = new FormData(this)
			fetch("update_purchase.php", {
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
								// Optionally, refresh the page to reflect changes after a slight delay
								setTimeout(() => location.reload(), 2000)
							},
							{ once: true }
						)
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
	document.querySelectorAll(".deleteBtn").forEach((button) => {
		button.addEventListener("click", function () {
			const purchaseId = this.getAttribute("data-id")
			// Populate the delete product modal with the purchase ID
			document.getElementById("delete_purchase_id").value = purchaseId

			// Show the delete modal
			const deleteModal = new bootstrap.Modal(
				document.getElementById("deleteModal")
			)
			deleteModal.show()
		})
	})

	// Confirm delete button listener
	const confirmDeleteBtn = document.getElementById("confirmDeleteBtn")
	if (confirmDeleteBtn) {
		confirmDeleteBtn.addEventListener("click", function (event) {
			event.preventDefault() // Prevent default form submission

			// Create FormData object
			const formData = new FormData()
			const purchaseId =
				document.getElementById("delete_purchase_id").value
			formData.append("purchase_id", purchaseId)

			// AJAX request to delete the purchase
			fetch("delete_purchase.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					if (data.success) {
						// Find and remove the table row
						const row = document.querySelector(
							`tr[data-purchase-id="${purchaseId}"]`
						)
						if (row) {
							row.remove()
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
									"Purchase deleted successfully."
								)
							},
							{ once: true }
						)
					} else {
						showAlert(
							"danger",
							"Error deleting purchase: " + data.message
						)
					}
				})
				.catch((error) => {
					console.error("Error:", error)
					showAlert("danger", "An unexpected error occurred.")
				})
		})
	}
})
