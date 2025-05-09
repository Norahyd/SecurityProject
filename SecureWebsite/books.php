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

// ADMIN-ONLY ACTION: Only allow book deletion if user is an admin
// Prevents unauthorized users from triggering critical operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id']) && $_SESSION['role'] === 'admin') {
    $book_id = intval($_POST['delete_book_id']);

    // SECURITY: Use prepared statements to avoid SQL injection
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();

    // Refresh the page to reflect the updated book list
    header("Location: books.php");
    exit();
}

// Fetch all books for display
$result = $conn->query("SELECT * FROM books");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Books</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f8; margin: 0; }
        nav { background: #6a5acd; padding: 12px; text-align: center; }
        nav a { color: white; margin: 0 10px; text-decoration: none; font-weight: bold; }
        .container { max-width: 900px; margin: auto; padding: 20px; }
        .book-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .book-info { flex-grow: 1; }
        .book-img { margin-right: 20px; }
        .book-img img { width: 80px; height: auto; border-radius: 5px; }
        button {
            background: #4b3ea7; color: white;
            border: none; padding: 8px 12px;
            border-radius: 5px; cursor: pointer;
            margin-left: 10px;
        }
        button:hover { background: #e8491d; }
    </style>
</head>
<body>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="books.php">View Books</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="addbook.php">Add Book</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Your Book List</h2>
    <?php while ($book = $result->fetch_assoc()): ?>
        <div class="book-card">
            <div class="book-img">
                <?php if ($book['image']): ?>
                    <!-- Sanitize image path to prevent HTML injection -->
                    <img src="uploads/<?php echo htmlspecialchars($book['image']); ?>" alt="Cover">
                <?php endif; ?>
            </div>
            <div class="book-info">
                <!-- Output sanitized to prevent XSS -->
                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            </div>
            <div>
                <!-- View Reviews (everyone can see) -->
                <a href="reviews.php?book_id=<?php echo $book['id']; ?>"><button>View Reviews</button></a>

                <!-- Delete Button (visible only to admins) -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="delete_book_id" value="<?php echo $book['id']; ?>">
                        <button onclick="return confirm('Delete this book?')">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>