document.addEventListener("DOMContentLoaded", function () {
	// Show custom alert function with fade-in and fade-out effects
	function showAlert(message, type = "success") {
		const alertBox = document.createElement("div");
		alertBox.className = `alert alert-${type} custom-alert`;
		alertBox.innerText = message;

		// Ensure the alert is positioned at the top
		alertBox.style.position = "fixed";
		alertBox.style.top = "10px";
		alertBox.style.left = "50%";
		alertBox.style.transform = "translateX(-50%)";
		alertBox.style.zIndex = "9999";
		document.body.appendChild(alertBox);

		// Add a fade-in effect
		alertBox.style.opacity = "0";
		setTimeout(() => {
			alertBox.style.transition = "opacity 0.3s"; // Transition effect
			alertBox.style.opacity = "1";
		}, 0); // Delay to allow the element to be added to the DOM

		// Remove alert after 3 seconds
		setTimeout(() => {
			alertBox.style.opacity = "0"; // Fade out effect
			setTimeout(() => {
				document.body.removeChild(alertBox); // Remove from DOM after fade-out
			}, 300); // Wait for fade-out to complete
		}, 5000); // Alert disappears after 3 seconds
	}

	// Handle form submission for profile update
	document.querySelector("form").addEventListener("submit", function (event) {
		event.preventDefault(); // Prevent default form submission

		const formData = new FormData(this);

		fetch("../models/update_profile.php", {
			method: "POST",
			body: formData,
		})
			.then((response) => response.json())
			.then((data) => {
				// Display the alert based on the response
				if (data.success) {
					showAlert(data.message, "success"); // Show success alert
				} else {
					showAlert(data.message, "danger"); // Show error alert
				}
			})
			.catch((error) => {
				console.error("Error:", error);
				showAlert(
					"An error occurred while updating profile.",
					"danger"
				); // Show error alert
			});
	});

	// Handle form submission for password change
	const form = document.querySelector("#changePasswordForm"); // Ensure correct form ID
	if (form) {
		form.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent default form submission

			const formData = new FormData(form);

			fetch("../models/change_password.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					if (data.success) {
						showAlert(data.message, "success"); // Show success alert
						form.reset(); // Clear the input fields
					} else {
						showAlert(data.message, "danger"); // Show error alert
					}
				})
				.catch((error) => {
					console.error("Error:", error);
					showAlert(
						"An error occurred while changing password.",
						"danger"
					); // Show error alert
				});
		});
	} else {
		console.error("Password change form not found!");
	}

	const profilePictureForm = document.getElementById("profilePictureForm");
	if (profilePictureForm) {
		profilePictureForm.addEventListener("submit", function (event) {
			event.preventDefault(); // Prevent default form submission

			const formData = new FormData(profilePictureForm);

			fetch("../models/update_profilepic.php", {
				method: "POST",
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					showAlert(
						data.message,
						data.success ? "success" : "danger"
					); // Show success or error alert
					profilePictureForm.reset();
				})
				.catch((error) => {
					console.error("Error:", error);
					showAlert(
						"An error occurred while uploading the picture.",
						"danger"
					);
				});
		});
	} else {
		console.error("Profile picture form not found!");
	}
});
