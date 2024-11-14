<?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: ../public/login.php');
        exit();
    }

    if ($_SESSION['user_type'] !== 'admin') {
        header('Location: ../public/login.php');
        exit();
    }

    include '../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body>
    <div class="main-content" id="main-content">
        <header>
            <h1>Archived Users</h1>
            <div class="admin-profile">
                <img src="../assets/images/account-avatar-profile-user-14-svgrepo-com.png" alt="Admin">
                <span>Administrator</span>
            </div>
        </header>
        <div class="table-content">
            <div class="table-list" id="userTableContainer">
                <a href="Users.php">
                    <button class="btn btn-primary custombtn" id="backBtn">
                        <i class="fas fa-arrow-left me-2"></i> Back to Users
                    </button>
                </a>
                <table>
                    <thead> 
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
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
                        $archived_users_per_page = 10; // Number of users per page
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($current_page - 1) * $archived_users_per_page;

                        // Fetch archived users with limit and offset
                        $sql = "SELECT * FROM user_management WHERE archived = 1 ORDER BY user_id DESC LIMIT $offset, $archived_users_per_page";
                        $result = $conn->query($sql);

                        // Fetch total number of archived users for pagination
                        $total_archived_users_sql = "SELECT COUNT(*) AS total FROM user_management WHERE archived = 1";
                        $total_result = $conn->query($total_archived_users_sql);
                        $total_row = $total_result->fetch_assoc();
                        $total_archived_users = $total_row['total'];
                        $total_pages = ceil($total_archived_users / $archived_users_per_page);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-user-id='" . $row["user_id"] . "'>
                                        <td>" . $row["user_id"] . "</td>
                                        <td>" . $row["username"] . "</td>
                                        <td>" . $row["email"] . "</td>
                                        <td>" . $row["user_type"] . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No archived users found</td></tr>";
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../controllers/users.js"></script>
</body>
</html>
