<?php
session_start();

// Initialize variables to avoid undefined variable errors
$username = "";
$usernameError = $passwordError = "";  // Initialize error variables

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    // Retrieve form data
    $username = isset($_POST["username"]) ? $_POST['username'] : '';
    $password = isset($_POST["password"]) ? $_POST['password'] : '';

    // Database connection details
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "pos&inventory";

    // Create a connection using mysqli with error handling
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the SQL query to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user_management WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $storedPasswordHash = $user_data["password"];

        // Verify the password
        if (password_verify($password, $storedPasswordHash)) {
            // Successful login
            $user_type = $user_data['user_type'];
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $user_type;

            // Redirect based on user type
            if ($user_type === 'admin') {
                header("Location: dashboard.php");
            } elseif ($user_type === 'cashier') {
                header("Location: cashier.php");
            } 
            exit;
        } else {
            // Password doesn't match
            $passwordError = "Password is incorrect.";
        }
    } else {
        // Username not found
        $usernameError = "Username not found.";
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Inventory System</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1>Welcome back!</h1>
            <img src="images/V.png" alt="Grocery Illustration">
        </div>
        <div class="right-panel">
            <img src="images/logo.png" alt="Logo" class="logo">

            <h2>LOGIN</h2>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>">
                    <!-- Display username error message -->
                    <p class="error"><?php echo $usernameError; ?></p>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <!-- Display password error message -->
                    <p class="error"><?php echo $passwordError; ?></p>
                    <span class="toggle-password"></span>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
    <script src="LOGIN.js"></script>
</body>
</html>
