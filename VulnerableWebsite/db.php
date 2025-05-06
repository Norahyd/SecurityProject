<?php
$host = "localhost";
$user = "root";
$pass = ""; // empty by default in XAMPP
$db = "book_reviews";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>