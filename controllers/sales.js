// Function to convert 12-hour time to 24-hour format
function convertTo24HourFormat(datetime) {
	const [date, time, period] = datetime.split(" ");
	let [hour, minute, second] = time.split(":");

	// Convert hour to 24-hour format
	if (period === "PM" && hour !== "12") {
		hour = parseInt(hour, 10) + 12;
	} else if (period === "AM" && hour === "12") {
		hour = "00";
	}

	// Return datetime in 24-hour format
	return `${date} ${hour}:${minute}:${second}`;
}

let currentPage = 1;
const rowsPerPage = 10;

// Function to paginate rows
function paginateRows(rows) {
	const totalRows = rows.length;
	const totalPages = Math.ceil(totalRows / rowsPerPage);

	// Hide all rows
	rows.forEach((row, index) => {
		row.style.display =
			index >= (currentPage - 1) * rowsPerPage &&
			index < currentPage * rowsPerPage
				? ""
				: "none";
	});

	// Update pagination controls
	updatePaginationControls(totalPages);
}

// Function to update pagination controls
function updatePaginationControls(totalPages) {
	const paginationContainer = document.getElementById("pagination");
	paginationContainer.innerHTML = "";

	// Add "Previous" button
	const prevButton = document.createElement("button");
	prevButton.textContent = "Previous";
	prevButton.disabled = currentPage === 1;
	prevButton.addEventListener("click", () => {
		if (currentPage > 1) {
			currentPage--;
			applyFilters(); // Reload filters with updated page
		}
	});
	paginationContainer.appendChild(prevButton);

	// Add page numbers
	for (let i = 1; i <= totalPages; i++) {
		const pageButton = document.createElement("button");
		pageButton.textContent = i;
		pageButton.classList.add("page-button");
		if (i === currentPage) {
			pageButton.classList.add("active");
		}
		pageButton.addEventListener("click", () => {
			currentPage = i;
			applyFilters(); // Reload filters with updated page
		});
		paginationContainer.appendChild(pageButton);
	}

	// Add "Next" button
	const nextButton = document.createElement("button");
	nextButton.textContent = "Next";
	nextButton.disabled = currentPage === totalPages;
	nextButton.addEventListener("click", () => {
		if (currentPage < totalPages) {
			currentPage++;
			applyFilters(); // Reload filters with updated page
		}
	});
	paginationContainer.appendChild(nextButton);
}

// Function to apply filters and reload the page
function applyFilters() {
	const searchInput = document.getElementById("searchInput").value;
	const startDate = document.getElementById("startDate").value;
	const endDate = document.getElementById("endDate").value;
	const cashier = document.getElementById("cashierDropdown").value;

	const urlParams = new URLSearchParams({
		search: searchInput,
		startDate: startDate,
		endDate: endDate,
		cashier: cashier,
		page: currentPage, // Include the current page
	});

	window.location.href = "sales.php?" + urlParams.toString();
}

// Add event listeners for inputs to apply filters dynamically
document
	.getElementById("searchInput")
	.addEventListener("keypress", function (event) {
		if (event.key === "Enter") {
			applyFilters();
		}
	});

document.getElementById("startDate").addEventListener("change", () => {
	currentPage = 1; // Reset to the first page
	applyFilters();
});

document.getElementById("endDate").addEventListener("change", () => {
	currentPage = 1; // Reset to the first page
	applyFilters();
});

document.getElementById("cashierDropdown").addEventListener("change", () => {
	currentPage = 1; // Reset to the first page
	applyFilters();
});
