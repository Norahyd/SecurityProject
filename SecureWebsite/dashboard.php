<?php
session_start();
require 'db.php';

// SECURITY CHECK: Ensure the session_token cookie exists
// We use cookies for persistent login. Without a token, redirect to login.
if (!isset($_COOKIE['session_token'])) {
    header("Location: login.php");
    exit();
}

// Validate the session token by looking it up in the database
// This prevents forged cookies from granting access
$token = $_COOKIE['session_token'];
$stmt = $conn->prepare("SELECT * FROM users WHERE session_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

// If token is invalid or not found, redirect to login
if ($result->num_rows !== 1) {
    header("Location: login.php");
    exit();
}

// Token is valid, fetch user info
$user = $result->fetch_assoc();

// Restore session variables if they were lost (e.g., after browser restart)
// This enables consistent session usage across pages
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// Fetch statistics for dashboard metrics
// These values are used in the UI to show platform-wide activity
$book_count   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM books"))[0];
$review_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews"))[0];
$user_count   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f8;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #6a5acd;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }
        nav {
            background-color: #4b3ea7;
            padding: 10px;
            text-align: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            gap: 20px;
        }
        .stat-box {
            background: #eee;
            padding: 20px;
            border-radius: 10px;
            width: 30%;
        }
        .stat-box h3 {
            margin-bottom: 10px;
            color: #4b3ea7;
        }
        .stat-box p {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Use htmlspecialchars (used to convert special characters to HTML entities) to prevent XSS in user-generated output -->
    <header>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?> to your digital library</header>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="books.php">Books</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="container">
        <h2>Dashboard</h2>
        <p>Overview of your library system.</p>
        <div class="stats">
            <div class="stat-box">
                <h3>Total Books</h3>
                <p><?php echo $book_count; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Reviews</h3>
                <p><?php echo $review_count; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $user_count; ?></p>
            </div>
        </div>
    </div>
</body>
</html>