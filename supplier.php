<?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

    if ($_SESSION['user_type'] !== 'admin') {
        header('Location: login.php');
        exit();
    }    

    include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Supplier Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="main.css">
   
</head>
<body>

    <div class="main-content" id="main-content">
        <header>
        <h1>Supplier Management</h1>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
        <div class="table-content" id="supplier">
            <section class="table-list">
                <button class="btn btn-primary add-supplier-btn custom-btn float-right" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-add me-2"></i>Add Supplier</button>
                <table class="Table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
							<th>Name</th>
							<th>Address</Address></th>
							<th>Contact No.</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                             
                        // Database connection
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $dbname = "pos&inventory"; // Changed to a valid DB name

                        // Create connection
                        $conn = new mysqli($servername, $username, $password, $dbname);

                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                         // Pagination variables
                         $supplier_per_page = 10; // Number of products per page
                         $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                         $offset = ($current_page - 1) * $supplier_per_page;
 
                         // Fetch total number of products for pagination
                         $total_supplier_sql = "SELECT COUNT(*) AS total FROM suppliers";
                         $total_result = $conn->query($total_supplier_sql);
                         $total_row = $total_result->fetch_assoc();
                         $total_supplier = $total_row['total'];
                         $total_pages = ceil($total_supplier / $supplier_per_page);
                        
                        // Process form submission for adding a new purchase
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_supplier'])) {
                            $name = $_POST['name'];
                            $address = $_POST['address'];
                            $contact_num = $_POST['contact_num'];
                        
                     // Prepare and bind
                     $stmt = $conn->prepare("INSERT INTO suppliers (name, address, contact_num) VALUES  (?, ?, ?)");
                     $stmt->bind_param("sss", $name, $address, $contact_num);

                     if ($stmt->execute()) {
                        // Trigger JavaScript alert for successful addition
                        echo "<script>
                            window.onload = function() {
                                showAlert('Supplier added successfully!', 'success');
                            };
                            setTimeout(function() {
                                window.location.href = 'supplier.php'; // Redirect after 3 seconds
                            }, 3000);
                        </script>";
                    } else {
                        // Show error alert in case of failure
                        echo "<script>
                            window.onload = function() {
                                showAlert('Error: Could not add supplier.', 'danger');
                            };
                        </script>";
                    }
                    $stmt->close();
                    }

                 // Fetch supplier from the database in descending order
                 $sql = "SELECT * FROM suppliers ORDER BY supplier_id DESC";
                 $result = $conn->query($sql);

                 if ($result->num_rows > 0) {
                     while($row = $result->fetch_assoc()) {
                         echo  "<tr data-supplier-id='" . $row["supplier_id"] . "'>
                                 <td>" . $row["supplier_id"] . "</td>
                                 <td>" . $row["name"] . "</td>
                                 <td>" . $row["address"] . "</td>
                                 <td>" . $row["contact_num"] . "</td>
                                 <td>
                                      <button class='btn btn-success editBtn' id='editBtn' data-id='" . $row['supplier_id'] . "' ><i class='fas fa-edit me-2'></i>Edit</button> |
                                         <button class='btn btn-danger deleteBtn' id='editBtn' data-id='" . $row['supplier_id'] . "'><i class='fas fa-trash me-2'></i>Delete</button>
                                 </td>
                               </tr>";
                     }
                 } else {
                     echo "<tr><td colspan='5'>No supplier found</td></tr>";
                 }
                 $conn->close();
             ?>
         </tbody>
     </table>
     <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <a href="?page=<?php echo $page; ?>"<?php echo $page == $current_page ? ' class="active"' : ''; ?>>
                            <?php echo $page; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
     <!-- Add  Modal -->
     <div id="addModal" class="modal fade" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="addModalLabel">Add New Supplier</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addForm" action="supplier.php" method="POST">
                            <input type="hidden" name="add_supplier" value="1">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name:</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address:</label>
                                <input type="text" id="address" name="address" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact_num" class="form-label">Contact No.:</label>
                                <input type="text" id="contact_num" name="contact_num" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary custom-btn-color">Add Supplier</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit  Modal -->
        <div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="editModalLabel">Edit Supplier</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm" action="update_supplier.php" method="POST">
                            <input type="hidden" id="edit_supplier_id" name="supplier_id">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Name:</label>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_address" class="form-label">Address:</label>
                                <input type="text" id="edit_address" name="address" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_contact_num" class="form-label">Contact No.:</label>
                                <input type="text" id="edit_contact_num" name="contact_num" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary custom-btn-color">Update Supplier</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete  Modal -->
        <div id="deleteModal" class="modal fade" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="deleteModalLabel">Delete Supplier</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this supplier?</p>
                        <form id="deleteForm" action="delete_supplier.php" method="POST">
                            <input type="hidden" id="delete_supplier_id" name="supplier_id">
                            <div class="modal-footer">
                            <button type="submit" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="supplier.js"></script>
</body>
</html>
