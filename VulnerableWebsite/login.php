<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start a session to maintain user login state
session_start();
require_once 'db.php';

$error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs from the form
    $username = $_POST["username"];
    $password = md5($_POST['password']); // 🚨 Insecure: MD5 is weak for password storage, vulnerable to hash collisions

    // 🚨 This query is vulnerable to SQL Injection. 
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    // Check if user exists and credentials match
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];
        header("Location: dashboard_vulnerable.php"); // Redirect to the dashboard
        exit;
    } else {
        // Invalid login credentials
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            margin: 0; padding: 0;
            font-family: Arial, sans-serif;
            background: #eef3f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="text"], input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #005b96;
            color: white;
            cursor: pointer;
            border: none;
        }
        input[type="submit"]:hover {
            background-color: #003f6f;
        }
        .error { color: red; margin-bottom: 10px; }
        .link {
            margin-top: 15px;
            text-align: center;
        }
        .link a {
            color: #005b96;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div> <!-- Display error message if credentials are invalid -->
        <?php endif; ?>

        <form method="post">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="submit" value="Login">
        </form>

        <div class="link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
