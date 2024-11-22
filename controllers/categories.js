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
			// Add 100ms delay before showing the alert
			setTimeout(() => {
				showAlert(message, type);
				sessionStorage.removeItem("alertMessage");
				sessionStorage.removeItem("alertType");
			}, 100); // Delay the alert display by 250ms
		}
	}

	// Add form submission event listener
	document
		.getElementById("addForm")
		.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent default form submission

			const formData = new FormData(this); // Capture form data

			// Make a POST request
			fetch("../models/create_category.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					const addModalEl = document.getElementById("addModal");
					const addModal = bootstrap.Modal.getInstance(addModalEl);

					// Hide the modal first
					addModal.hide();

					// Wait for the modal to fully hide before refreshing the page
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
							// Refresh the page
							setTimeout(() => location.reload(), 0); // 100ms delay before reload
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
					//location.reload();
				});
		});

	// Function to open the edit modal with pre-filled data
	function openEditModal(category_id) {
		fetch(`../models/get_category.php?category_id=${category_id}`)
			.then((response) => response.json())
			.then((data) => {
				document.getElementById("edit_category_id").value =
					data.category_id;
				document.getElementById("edit_category_name").value =
					data.category_name;

				const editModal = new bootstrap.Modal(
					document.getElementById("editModal")
				);
				editModal.show();
			})
			.catch((error) => {
				console.error("Error:", error);
				sessionStorage.setItem(
					"alertMessage",
					"An error occurred while fetching category data."
				);
				sessionStorage.setItem("alertType", "danger");
			});
	}

	// Edit form submission (move this outside the click listener)
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

	// Confirm delete button listener (move this outside the click listener)
	document
		.getElementById("confirmDeleteBtn")
		.addEventListener("click", function (event) {
			event.preventDefault(); // Prevent default button action (form submission)

			const categoryId =
				document.getElementById("delete_category_id").value;

			fetch(`../models/delete_category.php?category_id=${categoryId}`, {
				method: "GET",
			}) // Use category_id
				.then((response) => response.json())
				.then((data) => {
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
					sessionStorage.setItem(
						"alertMessage",
						"An unexpected error occurred."
					);
					sessionStorage.setItem("alertType", "danger");
				});
		});

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
	});

	checkAlertFromSession(); // Check and show alert if any message exists
});
