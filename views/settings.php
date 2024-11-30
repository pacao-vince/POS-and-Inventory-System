<?php
session_start();

require_once '../includes/db_connection.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit;
}

include '../includes/sidebar.php';

// Get the user ID from the session
$userId = $_SESSION['user_id'];

// Fetch user details from the database
$sql = "SELECT username, email, profile_picture FROM user_management WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $email, $profilePicture);
$stmt->fetch();
$stmt->close();

// If no user found, exit
if (!$username || !$email) {
    echo "User not found.";
    exit;
}

// Set default image if no profile picture is found
$profilePicture = $profilePicture ? '../assets/uploads/profile_pictures/' . $profilePicture : '../assets/images/account-avatar-profile-user-14-svgrepo-com.png';
?>

<!-- settings.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/setting.css">
</head>
<body>

    <div class="container mt-4">
        <h2 class="mb-3">Profile Settings</h2>
        
        <!-- Profile Information Update Form -->
        <div class="card">
            <div class="card-body">
                <h5>Update Profile Information</h5>
                <form action="../models/update_profile.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
        
        <!-- Change Password Form -->
        <div class="card mt-4">
            <div class="card-body">
                <h5>Change Password</h5>
                <form id="changePasswordForm" action="../models/change_password.php" method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>

        <!-- Update Profile Picture Form -->
        <div class="card mt-4">
            <div class="card-body">
                <h5>Update Profile Picture</h5>
                <form action="../models/update_profilepic.php" id="profilePictureForm" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Choose New Profile Picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Picture</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../controllers/settings.js"></script>
</body>
</html>
