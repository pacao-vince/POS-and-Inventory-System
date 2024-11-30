<?php
session_start(); // Start session for user management

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define the upload directory
$uploadDir = '../assets/uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
}

// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $fileName = basename($file['name']);
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = mime_content_type($fileTmpPath);

    // Validate file size (max 50MB)
    $maxFileSize = 50 * 1024 * 1024; // 50MB
    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the maximum limit of 2MB.']);
        exit;
    }

    // Validate file type (only images)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/bmp', 'image/tif', 'image/tiff', 'image/heif', 'image/svg'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Allowed file formats are: JPG, JPEG, PNG, BMP, TIFF, WEBP, HEIF and SVG']);
        exit;
    }

    // Generate a unique file name
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('profile_', true) . '.' . $fileExtension;

    // Move the file to the upload directory
    $uploadPath = $uploadDir . $newFileName;
    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        // Update the user's profile picture in the database
        require '../includes/db_connection.php'; // Include database connection

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User is not logged in.']);
            exit;
        }
        $userId = $_SESSION['user_id']; // Get user ID from session

        // Use $conn for the database query
        $stmt = $conn->prepare("UPDATE user_management SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param('si', $newFileName, $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload the file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or invalid request.']);
}
?>
