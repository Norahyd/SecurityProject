<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//  Database connection
require_once 'db.php';

$error = "";
$success = "";

//  Handle POST request for registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //  Vulnerable: no input sanitization or validation
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    //  Vulnerability: allows empty or malicious input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } else {
        //  Insecure: using MD5 for password hashing (fast, no salt, easily cracked)
        //  In secure version, we used password_hash($password, PASSWORD_BCRYPT)
        $hashed_password = md5($password);

        //  SQL Injection Vulnerability:
        // If user inputs SQL code (e.g., DROP TABLE users), it will be executed directly
        if (preg_match('/^\s*(INSERT|DROP|UPDATE|DELETE|ALTER|CREATE)\s+/i', $username)) {
            $sql = $username; // Attacker controls full SQL — this is intentional
        } else {
            //  Insecure SQL string interpolation — vulnerable to SQL injection
            $sql = "INSERT INTO users (username, email, password, role)
                    VALUES ('$username', '$email', '$hashed_password', 'user')";
        }

        //  Using multi_query() to allow execution of multiple SQL commands
        if ($conn->multi_query($sql) === TRUE) {
            $success = "Registration successful.";
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vulnerable Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f0f9ff;
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
            color: #005b96;
        }
        input {
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
        .success { color: green; margin-bottom: 10px; }
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
        <h2>Register</h2>

        <!--  Display error or success messages -->
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!--  No CSRF protection, no input validation -->
        <form method="post">
            <input type="text" name="username" placeholder="Username">
            <input type="text" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <input type="password" name="confirm_password" placeholder="Confirm Password">
            <input type="submit" value="Register">
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>