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

// SECURITY: Restrict this page to admin users only
// Ensures only authorized roles can access book-adding functionality
if ($user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Restore session variables if they were lost (e.g., after browser restart)
// This enables consistent session usage across pages
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// Handle book addition on POST request
// Books are stored with title, author, and image path
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];

    // SECURITY NOTE: basename() prevents directory traversal (allows to extract only the name of a file from a whole path)
    $image = basename($_FILES['image']['name']);
    $target = "uploads/" . $image;

    // Only move file if it was uploaded successfully
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO books (title, author, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $author, $image);
        $stmt->execute();

        // Provide feedback and redirect
        echo "<script>alert('Book added!'); window.location.href='books.php';</script>";
    } else {
        echo "<p style='color:red;'>Image upload failed.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Book (Secure)</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f8; }
        .container {
            max-width: 500px; margin: 60px auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 0 8px rgba(0,0,0,0.08);
        }
        input, button {
            width: 100%; padding: 10px; margin: 12px 0;
            border-radius: 6px; border: 1px solid #ccc;
        }
        button {
            background: #6a5acd; color: white; font-weight: bold; cursor: pointer;
        }
        button:hover { background: #4b3ea7; }
    </style>
</head>
<body>
<div class="container">
    <h2>Add New Book (Admin Only)</h2>
    <!-- Admin-only form for adding books -->
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Book Title" required>
        <input type="text" name="author" placeholder="Author" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Add Book</button>
    </form>
</div>
</body>
</html>