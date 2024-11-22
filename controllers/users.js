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
			fetch("../models/add_users.php", {
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

	// Edit button click event listener
	document.querySelectorAll(".editBtn").forEach(function (button) {
		button.addEventListener("click", function () {
			const userId = this.getAttribute("data-id");
			const username =
				this.closest("tr").querySelector("td:nth-child(2)").textContent;
			const email =
				this.closest("tr").querySelector("td:nth-child(3)").textContent;
			const userType =
				this.closest("tr").querySelector("td:nth-child(4)").textContent;

			// Set values in the modal
			document.getElementById("edit_user_id").value = userId;
			document.getElementById("edit_username").value = username;
			document.getElementById("edit_email").value = email;
			document.getElementById("edit_user_type").value =
				userType.toLowerCase();

			// Show the modal
			const editModal = new bootstrap.Modal(
				document.getElementById("editModal")
			);
			editModal.show();
		});
	});

	// Handle form submission for edit user via AJAX
	document
		.getElementById("editForm")
		.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent form submission from reloading the page

			const formData = new FormData(this);
			fetch("../models/update_user.php", {
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
	document.querySelectorAll(".deleteBtn").forEach(function (button) {
		button.addEventListener("click", function () {
			const userId = this.getAttribute("data-id");
			// Populate the delete user modal with the user ID
			document.getElementById("delete_user_id").value = userId;

			// Show the delete modal
			const deleteModal = new bootstrap.Modal(
				document.getElementById("deleteModal")
			);
			deleteModal.show();
		});
	});

	const deleteBtn = document.getElementById("confirmDeleteBtn");
	if (deleteBtn) {
		deleteBtn.addEventListener("click", function () {
			// Create FormData object
			const formData = new FormData();
			const userId = document.getElementById("delete_user_id").value;
			formData.append("user_id", userId);

			// AJAX request to delete the user
			fetch("../models/delete_user.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json()) // Ensure server returns JSON
				.then((data) => {
					if (data.success) {
						const row = document.querySelector(
							`tr[data-user-id="${userId}"]`
						);
						if (row) {
							row.remove();

							const deleteModalEl =
								document.getElementById("deleteModal");
							const deleteModal =
								bootstrap.Modal.getInstance(deleteModalEl);

							// Hide the modal first
							deleteModal.hide();

							// Show alert after the modal is fully hidden
							deleteModalEl.addEventListener(
								"hidden.bs.modal",
								function () {
									showAlert(
										"User archived and deleted successfully.",
										"success"
									);
								},
								{ once: true }
							);
						}
					} else {
						showAlert(
							"Error deleting user: " + data.message,
							"danger"
						);
					}
				})
				.catch((error) => {
					console.error("Error:", error);
					showAlert("An unexpected error occurred.", "danger");
				});
		});
	}

	document
		.getElementById("archiveBtn")
		.addEventListener("click", function () {
			// Send an AJAX request to load archived users
			fetch("../views/archived_users.php")
				.then((response) => response.text())
				.then((data) => {
					document.getElementById("userTableContainer").innerHTML =
						data;
				});
		});

	checkAlertFromSession();
});
