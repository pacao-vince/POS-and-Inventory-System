<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pos&inventory";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        $sql = "DELETE FROM user_management WHERE user_id = $user_id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting user: " . $conn->error]);
        }
    }
}

$conn->close();
?>
