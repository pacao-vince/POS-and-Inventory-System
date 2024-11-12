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
    <title>POS System Category Management</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="main.css">

</head>
<body>

    <div class="main-content" id="main-content">
        <header>
            <h1>Category Management</h1>
            <div class="admin-profile">
                <img src="images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
        
        <div class="table-content">
            <div class="table-list">
                <button class="btn btn-primary add-category-btn custom-btn float-right" id="add-btn" data-bs-toggle="modal" data-bs-target="#addModal"><i class='fas fa-add me-2'></i>Add Category</button>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- PHP code for displaying categories -->
                        <?php
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "pos&inventory";
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Handle Add Category Form Submission
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
                        $category_name = $_POST['category_name'];
                        $stmt = $conn->prepare("INSERT INTO category (category_name) VALUES (?)");
                        if ($stmt) {
                            $stmt->bind_param("s", $category_name);
                            if ($stmt->execute()) {
                                echo "<script>
                                    window.onload = function() {
                                        showAlert('Category added successfully!', 'success');
                                    };
                                    setTimeout(function() {
                                        window.location.href = 'categories.php';
                                    }, 3000);
                                </script>";
                            } else {
                                echo "<script>
                                    window.onload = function() {
                                        showAlert('Error: Could not add category.', 'danger');
                                    };
                                </script>";
                            }
                            $stmt->close();
                        }
                    }

                    // Pagination logic
                    $category_per_page = 10;
                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($current_page - 1) * $category_per_page;

                    $total_category_sql = "SELECT COUNT(*) AS total FROM category";
                    $total_result = $conn->query($total_category_sql);
                    $total_row = $total_result->fetch_assoc();
                    $total_category = $total_row['total'];
                    $total_pages = ceil($total_category / $category_per_page);

                    // Fetch categories with pagination
                    $sql = "SELECT * FROM category ORDER BY category_id DESC LIMIT $offset, $category_per_page";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr data-category-id='" . $row["category_id"] . "'>
                                    <td>" . $row["category_id"] . "</td>
                                    <td>" . $row["category_name"] . "</td>
                                    <td>
                                        <button class='btn btn-success editBtn' data-id='" . $row['category_id'] . "'><i class='fas fa-edit me-2'></i>Edit</button>
                                        <button class='btn btn-danger deleteBtn' data-id='" . $row['category_id'] . "'><i class='fas fa-trash me-2'></i>Delete</button>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No categories found</td></tr>";
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
            </div>
        </div>
        
        <!-- Add  Modal -->
        <div id="addModal" class="modal fade" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="addModalLabel">Add New Category</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addForm" action="categories.php" method="POST">
                            <input type="hidden" name="add_category" value="1">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name:</label>
                                <input type="text" id="category_name" name="category_name" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary custom-btn-color">Add Category</button>
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
                        <h2 class="modal-title" id="editModalLabel">Edit Category Name</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editCategoryForm" action="update_category.php" method="POST">
                            <input type="hidden" id="edit_category_id" name="category_id">
                            <div class="mb-3">
                                <label for="edit_category_name" class="form-label">Category Name:</label>
                                <input type="text" id="edit_category_name" name="category_name" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary custom-btn-color">Update Category</button>
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
                        <h2 class="modal-title" id="deleteModalLabel">Delete Category</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this category?</p>
                        <form id="deleteForm" action="delete_category.php" method="POST">
                            <input type="hidden" id="delete_category_id" name="category_id">
                            <div class="modal-footer">
                            <button type="submit" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="categories.js"></script>
</body>
</html>
