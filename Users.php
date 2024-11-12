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
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="main.css">
</head>

<body>
    <div class="main-content" id="main-content">
        <header>
        <h1>User Management</h1>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
        <div class="table-content">
            <div class="table-list">
                <button class="btn btn-primary custom-btn float-right" id="add-btn" data-bs-toggle="modal" data-bs-target="#addModal"><i class='fas fa-add me-2'></i>Add Account</button>

                <table class="table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    
                        // Database connection
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $dbname = "pos&inventory";

                        // Create connection
                        $conn = new mysqli($servername, $username, $password, $dbname);

                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // Pagination variables
                        $user_management_per_page = 10; // Number of users per page
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($current_page - 1) * $user_management_per_page;

                        // Fetch users with limit and offset
                        $sql = "SELECT * FROM user_management ORDER BY user_id DESC LIMIT $offset, $user_management_per_page";
                        $result = $conn->query($sql);

                        // Fetch total number of users for pagination
                        $total_user_management_sql = "SELECT COUNT(*) AS total FROM user_management";
                        $total_result = $conn->query($total_user_management_sql);
                        $total_row = $total_result->fetch_assoc();
                        $total_user_management = $total_row['total'];
                        $total_pages = ceil($total_user_management / $user_management_per_page);

                        // Fetch users from database
                        $sql = "SELECT * FROM user_management ORDER BY user_id DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            // Output data of each row
                             // Fetch users from the database
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr data-user-id='" . $row["user_id"] . "'>
                                        <td>" . $row["user_id"] . "</td>
                                        <td>" . $row["username"] . "</td>
                                        <td>" . $row["email"] . "</td>
                                        <td>" . $row["user_type"] . "</td>
                                        <td>
                                            <button class='btn btn-success editBtn' id='editBtn' data-id='" . $row['user_id'] . "'><i class='fas fa-edit me-2'></i>Edit</button> |
                                            <button class='btn btn-danger deleteBtn' id='editBtn' data-id='" . $row['user_id'] . "'><i class='fas fa-trash me-2'></i>Delete</button>
                                        </td>
                                    </tr>";
                            }

                        } else {
                            echo "<tr><td colspan='8'>No users found</td></tr>";
                        }

                        if (isset($_SESSION['success'])) {
                            echo "<script>showAlert('" . $_SESSION['success'] . "', 'success');</script>";
                            unset($_SESSION['success']); // Clear the message after displaying
                        }
                        
                        // Display error message
                        if (isset($_SESSION['error'])) {
                            echo "<script>showAlert('" . $_SESSION['error'] . "', 'danger');</script>";
                            unset($_SESSION['error']); // Clear the message after displaying
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
                        <a href="?page=<?php echo $page; ?>"<?php echo $page == $current_page ? ' class="active"' : ''; ?>><?php echo $page; ?></a>
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
    
<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="editModalLabel">Edit User</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="update_user.php" method="POST">
                    <input type="hidden" id="edit_user_id" name="user_id">

                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_user_type" class="form-label">User Type:</label>
                        <select class="form-select" id="edit_user_type" name="user_type" required>
                            <option value="admin">Admin</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary custom-btn-color"style="background-color:#2980b9;">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="alert-container"></div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="deleteModalLabel">Delete User</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deleteForm" action="delete_user.php" method="POST">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete this user?</p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

    
   <!-- Modal Structure -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="addModalLabel">Add New Account</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="add_users.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="mb-3">
                        <label for="userType" class="form-label">User Type</label>
                        <select class="form-select" id="userType" name="user_type" required>
                            <option value="admin">Admin</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                    <!-- Submit button must be inside the form -->
                    <div class="modal-footer">
                         <button type="submit" class="btn btn-primary custom-btn-color" id="submitUpdateBtn">Add Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="users.js"></script>
</body>    
</html>

