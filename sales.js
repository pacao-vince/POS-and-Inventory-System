// Function to convert 12-hour time to 24-hour format
function convertTo24HourFormat(datetime) {
	// Split the datetime into date and time components
	let [date, time, period] = datetime.split(" ")

	// Split time into hour, minute, and second
	let [hour, minute, second] = time.split(":")

	// Convert hour to 24-hour format
	if (period === "PM" && hour !== "12") {
		hour = parseInt(hour, 10) + 12
	} else if (period === "AM" && hour === "12") {
		hour = "00"
	}

	// Reconstruct the datetime in 24-hour format
	return `${date} ${hour}:${minute}:${second}`
}
document
	.getElementById("cashierDropdown")
	.addEventListener("change", filterTable)
document.getElementById("startDate").addEventListener("change", filterTable)
document.getElementById("endDate").addEventListener("change", filterTable)
document.getElementById("searchInput").addEventListener("keyup", filterTable)

function filterTable() {
	const searchInput = document
		.getElementById("searchInput")
		.value.toLowerCase()
	const startDate = document.getElementById("startDate").value
	const endDate = document.getElementById("endDate").value
	const cashier = document.getElementById("cashierDropdown").value

	const rows = document.querySelectorAll("#productsTable tbody tr")

	rows.forEach((row) => {
		const productName = row
			.querySelector(".product-name")
			.textContent.toLowerCase()
		const transactionDate = row.querySelector(".sale-date").textContent
		const cashierName = row.querySelector(".cashier").textContent

		let showRow = true

		// Filter by product name
		if (searchInput && !productName.includes(searchInput)) {
			showRow = false
		}

		// Filter by date range
		if (startDate || endDate) {
			const transactionDateObj = new Date(transactionDate)
			const startDateObj = startDate ? new Date(startDate) : null
			const endDateObj = endDate ? new Date(endDate) : null

			if (
				(startDate && transactionDateObj < startDateObj) ||
				(endDate && transactionDateObj > endDateObj)
			) {
				showRow = false
			}
		}

		// Filter by cashier
		if (cashier !== "All" && cashier !== cashierName) {
			showRow = false
		}

		// Show or hide row based on the filtering criteria
		row.style.display = showRow ? "" : "none"
	})
}
