window.showAlert = function (message, type = "success") {
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
};

function formatCurrency(amount) {
	return `₱${parseFloat(amount).toFixed(2)}`;
}

function formatDateToMySQLString(date) {
	const year = date.getFullYear();
	const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
	const day = String(date.getDate()).padStart(2, "0");
	const hours = String(date.getHours()).padStart(2, "0");
	const minutes = String(date.getMinutes()).padStart(2, "0");
	const seconds = String(date.getSeconds()).padStart(2, "0");

	return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function saveSale() {
	const savebtn = document.getElementById("confirmSaveSaleBtn"); // Use proper button ID
	savebtn.disabled = true; // Disable the button

	// Ensure products is accessed from the global object
	const products = Array.from(
		document.querySelectorAll("#productListBody tr")
	).map((row) => ({
		productId: row.cells[0].textContent,
		productName: row.cells[1].textContent,
		quantity: parseInt(row.cells[2].textContent),
		price: parseFloat(row.cells[3].textContent.replace("₱", "")),
		amount: parseFloat(row.cells[4].textContent.replace("₱", "")),
	}));

	const subTotal = parseFloat(
		document.getElementById("subTotal").textContent.replace("₱", "")
	);
	const grandTotal = parseFloat(
		document.getElementById("grandTotal").textContent.replace("₱", "")
	);
	const payment = parseFloat(document.getElementById("payment").value.trim());
	const change = parseFloat(
		document.getElementById("changeAmount").textContent.replace("₱", "")
	);

	const currentDateTime = new Date();
	const transactionTime = formatDateToMySQLString(currentDateTime); // Use MySQL date format
	const cashierName = document
		.querySelector(".cashier-profile span")
		.textContent.replace("Cashier, ", "")
		.trim();

	const data = {
		subTotal: subTotal,
		grandTotal: grandTotal,
		payment: payment,
		change: change,
		transactionTime: transactionTime,
		cashier_username: cashierName,
		products: products,
	};

	console.log("Captured Cashier Name:", cashierName); // Debugging line
	console.log("Data being sent to server:", data); // Debugging line

	fetch("../models/save_sale.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify(data),
	})
		.then((response) => {
			if (!response.ok) {
				return response.text().then((text) => {
					throw new Error(text);
				});
			}
			return response.json();
		})
		.then((result) => {
			if (result.success) {
				showAlert("Sale saved successfully!");
				// Clear table and reset payment fields after saving
				document.getElementById("productListBody").innerHTML = "";
				document.getElementById("payment").value = "";
				document.getElementById("changeAmount").textContent =
					formatCurrency(0);
				updateTotals();

				printReceipt(data); // Trigger receipt print function here
			} else {
				showAlert("Failed to save sale: ", "danger", +result.message);
			}
			savebtn.disabled = false; // Re-enable the button
		})
		.catch((error) => {
			console.error("Fetch error:", error); // Detailed error logging
			showAlert("Error: ", "danger", +error.message);
			savebtn.disabled = false; // Re-enable the button
		});
}

window.saveSale = saveSale;

function printReceipt(saleData) {
	fetch("../controllers/print.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		body: JSON.stringify(saleData),
	})
		.then((response) => {
			if (!response.ok) {
				return response.text().then((text) => {
					throw new Error(text);
				});
			}
			return response.json();
		})
		.then((result) => {
			if (result.success) {
				console.log("Receipt printed successfully");
			} else {
				showAlert(
					"Failed to print receipt:",
					"danger",
					+result.message
				);
			}
		})
		.catch((error) => {
			console.error("Print error:", error);
			showAlert("Error: ", "danger", +error.message);
		});
}
