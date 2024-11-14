<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Document</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
		integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
	<link rel="stylesheet" href="../assets/css/sidebar.css" />
</head>

<body>
	<div class="sidebar" id="sidebar">
		<div class="logo">
			<img src="../assets/images/logo.png" alt="Logo" />
			<h2>POS SYSTEM</h2>
		</div>
		<ul>
			<li>
				<a href="dashboard.php"><img src="../assets/images/dashboard-svgrepo-com.png"
						class="sidebar-icon" /><span>Dashboard</span></a>
			</li>
			<li>
				<a href="products.php"><img src="../assets/images/product-svgrepo-com.png"
						class="sidebar-icon" /><span>Products</span></a>
			</li>
			<li>
				<a href="sales.php"><img src="../assets/images/sales-up-graph-svgrepo-com.png"
						class="sidebar-icon" /><span>Sales</span></a>
			</li>
			<li>
				<a href="inventory.php"><img src="../assets/images/inventory-logistics-warehouse-svgrepo-com.png"
						class="sidebar-icon" /><span>Inventory</span></a>
			</li>
			<li>
				<a href="purchases.php"><img src="../assets/images/market-purchase-svgrepo-com.png"
						class="sidebar-icon" /><span>Purchase</span></a>
			</li>
			<li>
				<a href="categories.php"><img src="../assets/images/category-svgrepo-com.png"
						class="sidebar-icon" /><span>Categories</span></a>
			</li>
			<li>
				<a class="submenu-toggle" href="javascript:void(0)"><img src="../assets/images/analytics-svgrepo-com.png"
						class="sidebar-icon" alt="reports-icon" /><span>Reports </span>
				</a>
			</li>
			
			<li>
				<a href="supplier.php"><img src="../assets/images/supplier.png" class="sidebar-icon"><span>Supplier</span></a>
			</li>

			<li>
				<a href="barcode_generator.php"><img src="../assets/images/barcode.png" class="sidebar-icon"><span>Barcode Generator</span></a>
			</li>

			<li>
				<a href="Users.php"><img src="../assets/images/users-svgrepo-com.png" class="sidebar-icon"
						alt="users-icon" /><span>Users</span></a>
			</li>
		</ul>

		<ul>
			<li>
				<a href="#" data-toggle="modal" data-target="#logoutConfirmationModal">
					<img src="../assets/images/log-out-svgrepo-com.png" class="sidebar-icon" />
					<span>Log Out</span>
				</a>
			</li>
		</ul>

	</div>

	<!-- Logout Confirmation Modal -->
	<div class="modal fade" id="logoutConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-3 shadow-lg" style="padding: 20px; width: 350px;">
            <div class="modal-header border-0" style="padding: 0 10px;">
                <h3 class="modal-title w-100 text-center text-danger font-weight-bold" id="logoutModalLabel">Logout</h3>
            </div>
            <div class="modal-body text-center" style="padding: 10px 10px;">
                <p>Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer justify-content-center border-0" style="padding: 10px 10px;">
                <button type="button" id="confirmLogoutBtn" class="btn btn-danger w-25">Logout</button>
                <button type="button" class="btn btn-secondary w-25" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
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
				// Get the logout button that triggers the modal (assuming it has an ID "logoutButton")
			
            const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
            
            confirmLogoutBtn.addEventListener("click", function () {
                // Redirect to the actual logout URL
                window.location.href = "../controllers/logout.php";
            });

	})

</script>

</html>