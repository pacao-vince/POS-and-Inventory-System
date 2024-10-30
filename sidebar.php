<div class="sidebar" id="sidebar">
    <div class="logo">
        <img src="images/logo.png" alt="Logo">
        <h1>POS SYSTEM</h1>
    </div>
    <ul>
        <li><a href="dashboard.php"><img src="images/dashboard-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Dashboard</span></a></li>
        <li><a href="products.php"><img src="images/product-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Products</span></a></li>
        <li><a href="sales.php"><img src="images/sales-up-graph-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Sales</span></a></li>
        <li><a href="inventory.php"><img src="images/inventory-logistics-warehouse-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Inventory</span></a></li>
        <li><a href="purchases.php"><img src="images/market-purchase-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Purchase</span></a></li>
        <li><a href="categories.php"><img src="images/category-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Categories</span></a></li>

        <li>
            <a href="javascript:void(0)" class="submenu-toggle"><img src="images/analytics-svgrepo-com.png" class="sidebar-icon" width="24" height="24" alt="reports-icon"><span>Reports</span></a>
            <ul class="submenu">
                <li><a href="Stocks.php"><img src="images/stock-out.png"class="sidebar-icon" width="24" height="24">Stocks Report</a></li>
                <li><a href="dailySales.php"><img src="images/daily-sales.png"class="sidebar-icon" width="24" height="24">Daily Sales Report</a></li>
                <li><a href="monthlySales.php"><img src="images/monthlySales.png"class="sidebar-icon" width="24" height="24" >Monthly Sales Report</a></li>
            </ul>
        </li>

        <li><a href="supplier.php"><img src="images/supplier.png" class="sidebar-icon" width="24" height="24"><span>Supplier</span></a></li>
        <li><a href="Users.php"><img src="images/users-svgrepo-com.png" class="sidebar-icon" width="24" height="24" alt="users-icon"><span>Users</span></a></li>
    </ul>
    <ul class="logout">
        <li><a href="logout.php"><img src="images/log-out-svgrepo-com.png" class="sidebar-icon" width="24" height="24"><span>Log Out</span></a></li>
    </ul>
</div>

<script>
      document.addEventListener('DOMContentLoaded', function() {
        var submenuToggle = document.querySelector('.submenu-toggle');
        var submenu = submenuToggle.nextElementSibling; // Get the submenu element
        
        submenuToggle.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default anchor behavior
            
            this.classList.toggle('active');
            
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none'; // Close the submenu when Reports is clicked again
            } else {
                submenu.style.display = 'block'; // Open the submenu
            }
        });
        
        // Prevent the submenu from closing when a submenu item is clicked
        var submenuItems = document.querySelectorAll('.submenu a');
        submenuItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent the event from bubbling up to the submenu toggle
            });
        });
    });
</script>
