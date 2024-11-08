<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Document</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
		integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
	<link rel="stylesheet" href="sidebar.css" />
</head>

<body>
	<div class="sidebar" id="sidebar">
		<div class="logo">
			<img src="images/logo.png" alt="Logo" />
			<h2>POS SYSTEM</h2>
		</div>
		<ul>
			<li>
				<a href="dashboard.php"><img src="images/dashboard-svgrepo-com.png"
						class="sidebar-icon" /><span>Dashboard</span></a>
			</li>
			<li>
				<a href="products.php"><img src="images/product-svgrepo-com.png"
						class="sidebar-icon" /><span>Products</span></a>
			</li>
			<li>
				<a href="sales.php"><img src="images/sales-up-graph-svgrepo-com.png"
						class="sidebar-icon" /><span>Sales</span></a>
			</li>
			<li>
				<a href="inventory.php"><img src="images/inventory-logistics-warehouse-svgrepo-com.png"
						class="sidebar-icon" /><span>Inventory</span></a>
			</li>
			<li>
				<a href="purchases.php"><img src="images/market-purchase-svgrepo-com.png"
						class="sidebar-icon" /><span>Purchase</span></a>
			</li>
			<li>
				<a href="categories.php"><img src="images/category-svgrepo-com.png"
						class="sidebar-icon" /><span>Categories</span></a>
			</li>
			<li>
				<a class="submenu-toggle" href="javascript:void(0)"><img src="images/analytics-svgrepo-com.png"
						class="sidebar-icon" alt="reports-icon" /><span>Reports </span>
				</a>
				<!--ul class="submenu">
                            <li>
                                <a href="Stocks.php" style="font-size: 1.4rem; padding: 10px 20px 10px 40px;"
                                    ><!-img
                                        src="images/stock-out.png"
                                        class="sidebar-icon"
                                        
                                        
                                    />Stocks Report</a
                                >
                            </li>
                            <li>
                                <a href="dailySales.php" style="font-size: 1.4rem; padding: 10px 20px 10px 40px;"
                                    ><-!img
                                        src="images/daily-sales.png"
                                        class="sidebar-icon"
                                        
                                        
                                    /->Daily Sales Report</a
                                >
                            </li>
                            <li>
                                <a href="monthlySales.php" style="font-size: 1.4rem; padding: 10px 20px 10px 40px;"
                                    ><!-img
                                        src="images/monthlySales.png"
                                        class="sidebar-icon"
                                        
                                        
                                    /->Monthly Sales Report</a
                                >
                            </li>
                        </ul-->
			</li>
			
			<li>
				<a href="supplier.php"><img src="images/supplier.png" class="sidebar-icon"><span>Supplier</span></a>
			</li>

			<li>
				<a href="Users.php"><img src="images/users-svgrepo-com.png" class="sidebar-icon"
						alt="users-icon" /><span>Users</span></a>
			</li>
		</ul>
		<ul class="logout">
			<li>
				<a href="logout.php"><img src="images/log-out-svgrepo-com.png" class="sidebar-icon" /><span>Log
						Out</span></a>
			</li>
		</ul>
	</div>
</body>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
	integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
	crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
	integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
	crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
	integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
	crossorigin="anonymous"></script>
<script>
	document.addEventListener("DOMContentLoaded", function () {
		const submenuToggle = document.querySelector(".submenu-toggle")

		submenuToggle.addEventListener("click", function (e) {
			e.preventDefault() // Prevent the default anchor behavior

			// Check if the tooltip already exists
			if (this._tooltip) {
				this._tooltip.remove() // Remove tooltip if it exists
				this._tooltip = null // Clear the reference
				return // Exit the function
			}

			// Create the tooltip
			const tooltip = document.createElement("div")
			tooltip.className = "custom-tooltip" // Assign a class for styling

			// Create the inner HTML with links
			tooltip.innerHTML = `
                    <a href="Stocks.php" class="tooltip-link" style="text-decoration: none;">Stocks Report</a>
                    <a href="dailySales.php" class="tooltip-link" style="text-decoration: none;">Daily Sales Report</a>
                    <a href="monthlySales.php" class="tooltip-link" style="text-decoration: none;">Monthly Sales Report</a>
                `

			document.body.appendChild(tooltip)

			// Position the tooltip
			const rect = this.getBoundingClientRect()
			tooltip.style.left = rect.right + 10 + "px" // Positioning to the right of the link
			tooltip.style.top = rect.top + window.scrollY + "px" // Align vertically with the link

			// Show the tooltip
			tooltip.style.opacity = "1"
			this._tooltip = tooltip // Store the tooltip reference for later use
		})
		/*
		var submenuToggle = document.querySelector(".submenu-toggle")
		var submenu = submenuToggle.nextElementSibling // Get the submenu element

		submenuToggle.addEventListener("click", function (e) {
			e.preventDefault() // Prevent default anchor behavior

			this.classList.toggle("active")

			if (submenu.style.display === "block") {
				submenu.style.display = "none" // Close the submenu when Reports is clicked again
			} else {
				submenu.style.display = "block" // Open the submenu
			}
		})

		// Prevent the submenu from closing when a submenu item is clicked
		var submenuItems = document.querySelectorAll(".submenu a")
		submenuItems.forEach(function (item) {
			item.addEventListener("click", function (e) {
				e.stopPropagation() // Prevent the event from bubbling up to the submenu toggle
			})
		})
	*/
	})
</script>

</html>