<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'Database';  
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
