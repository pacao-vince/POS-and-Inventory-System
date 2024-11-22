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
			fetch("../models/create_supplier.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
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
		const editModal = new bootstrap.Modal(
			document.getElementById("editModal")
		);
		editModal.show();
	}

	// Handle form submission for editing product via AJAX
	document
		.getElementById("editForm")
		.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent form submission from reloading the page

			const formData = new FormData(this);
			fetch("../models/update_supplier.php", {
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
							setTimeout(() => location.reload(), 0); // 100ms delay before reload
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

	// Confirm delete button listener
	const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
	if (confirmDeleteBtn) {
		confirmDeleteBtn.addEventListener("click", function (event) {
			event.preventDefault(); // Prevent default form submission

			// Create FormData object for deleting
			const formData = new FormData();
			const supplierId =
				document.getElementById("delete_supplier_id").value;
			formData.append("supplier_id", supplierId);

			// AJAX request to delete the supplier
			fetch("../models/delete_supplier.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					if (data.success) {
						// Find and remove the table row
						const row = document.querySelector(
							`tr[data-supplier-id="${supplierId}"]`
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

						// Add alert to sessionStorage for display after reload
						sessionStorage.setItem("alertMessage", data.message);
						sessionStorage.setItem(
							"alertType",
							data.success ? "success" : "danger"
						);

						// Reload the page to reflect changes
						location.reload();
					} else {
						// Store an error message in sessionStorage
						sessionStorage.setItem("alertMessage", data.message);
						sessionStorage.setItem("alertType", "danger");
						location.reload();
					}
				})
				.catch((error) => {
					console.error("Error:", error);

					// Store a generic error message in sessionStorage
					sessionStorage.setItem(
						"alertMessage",
						"An unexpected error occurred."
					);
					sessionStorage.setItem("alertType", "danger");
					location.reload();
				});
		});
	}

	// Event delegation for Edit and Delete buttons
	document.addEventListener("click", function (event) {
		// Handle Edit button click
		if (event.target.classList.contains("editBtn")) {
			openEditModal(event.target);
		}

		// Handle Delete button click
		if (event.target.classList.contains("deleteBtn")) {
			const supplierId = event.target.getAttribute("data-id");
			document.getElementById("delete_supplier_id").value = supplierId;
			const deleteModal = new bootstrap.Modal(
				document.getElementById("deleteModal")
			);
			deleteModal.show();
		}
	});

	checkAlertFromSession(); // Check and show alert if any message exists
});
