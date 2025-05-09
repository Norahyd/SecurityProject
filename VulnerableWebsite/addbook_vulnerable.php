<?php
session_start();
require 'db.php';

// VULNERABILITY: Broken Access Control
// This page does NOT check whether the user is an admin
// Any logged-in user can access and use this page to add books
// This violates role-based permissions and lets unauthorized users modify data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];

    $image = basename($_FILES['image']['name']);
    $target = "uploads/" . $image;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $author, $image);
        $stmt->execute();

        // Book added, but still by unauthorized user
        echo "<script>alert('Book added!'); window.location.href='book_vulnerable.php';</script>";
    } else {
        echo "<p style='color:red;'>Image upload failed.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Book (Vulnerable)</title>
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
    <h2>Add New Book (Vulnerable)</h2>

    <!-- VULNERABILITY: Broken Access Control in UI -->
    <!-- No admin check to hide this form from regular users -->
    <!-- Any user can load this page and submit new book records -->

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Book Title" required>
        <input type="text" name="author" placeholder="Author" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Add Book</button>
    </form>
</div>
</body>
</html>