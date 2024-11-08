document.addEventListener("DOMContentLoaded", function () {
	// Fetch data from the server for the Sales Chart
	fetch("get_sales_data.php")
		.then((response) => response.json())
		.then((data) => {
			const salesChartElement = document.getElementById("salesChart")
			if (salesChartElement) {
				const salesCtx = salesChartElement.getContext("2d")
				new Chart(salesCtx, {
					type: "line",
					data: {
						labels: data.transaction_time, // Use the daily sales data
						datasets: [
							{
								label: "Sales",
								data: data.grand_total, // Use the sales amounts data
								borderColor: "rgba(52, 152, 219, 1)",
								borderWidth: 2,
								fill: false,
							},
						],
					},
					options: {
						responsive: true,
						scales: {
							x: {
								beginAtZero: true,
							},
							y: {
								beginAtZero: true,
							},
						},
					},
				})
			} else {
				console.error("Sales chart element not found.")
			}
		})
		.catch((error) => {
			console.error("Error fetching sales data:", error)
		})

	// Fetch data from the server for the Bestseller Chart
	fetch("get_bestseller_data.php")
		.then((response) => response.json())
		.then((data) => {
			const bestsellerChartElement =
				document.getElementById("bestsellerChart")
			if (bestsellerChartElement) {
				const bestsellerCtx = bestsellerChartElement.getContext("2d")

				// Generate dynamic colors based on the number of products
				const backgroundColors = []
				const borderColors = []
				for (let i = 0; i < data.product_name.length; i++) {
					backgroundColors.push(
						`rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(
							Math.random() * 255
						)}, ${Math.floor(Math.random() * 255)}, 2.3)`
					)
				}

				new Chart(bestsellerCtx, {
					type: "bar",
					data: {
						labels: data.product_name, // Use the product names data
						datasets: [
							{
								label: "Bestseller",
								data: data.total_amount, // Use the total sales amounts data
								backgroundColor: backgroundColors,
								borderColor: borderColors,
								borderWidth: 1,
							},
						],
					},
					options: {
						responsive: true,
						scales: {
							x: {
								beginAtZero: true,
							},
							y: {
								beginAtZero: true,
							},
						},
					},
				})
			} else {
				console.error("Bestseller chart element not found.")
			}
		})
		.catch((error) => {
			console.error("Error fetching bestseller data:", error)
		})
})

document.addEventListener("DOMContentLoaded", function () {
	const statItems = document.querySelectorAll(".stat-item")

	statItems.forEach((item) => {
		item.addEventListener("click", function () {
			const href = this.getAttribute("data-href")
			if (href) {
				window.location.href = href
			}
		})
	})
})
