<?php
require_once '../includes/db_connection.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit;
}

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

// Set default image if no profile picture is found
$profilePicture = $profilePicture ? '../assets/uploads/profile_pictures/' . $profilePicture : '../assets/images/account-avatar-profile-user-14-svgrepo-com.png';
?>

<!-- Dropdown profile component for dashboard -->
<div class="admin-profile dropdown">
    <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Admin" width="40" height="40" class="rounded-circle">
        <span class="ms-2"> Admin, <?php echo htmlspecialchars($username); ?></span> 
    </div>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="../views/settings.php">Settings</a></li>
    </ul>
</div>
