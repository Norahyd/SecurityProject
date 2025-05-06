<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle delete if admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id']) && $_SESSION['role'] === 'admin') {
    $book_id = intval($_POST['delete_book_id']);
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    header("Location: books.php");
    exit();
}

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
        button { background: #4b3ea7; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; margin-left: 10px; }
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
    <a href="login.php">Logout</a>
</nav>
<div class="container">
    <h2>Your Book List</h2>
    <?php while ($book = $result->fetch_assoc()): ?>
        <div class="book-card">
            <div class="book-img">
                <?php if ($book['image']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($book['image']); ?>" alt="Cover">
                <?php endif; ?>
            </div>
            <div class="book-info">
                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            </div>
            <div>
                <a href="reviews.php?book_id=<?php echo $book['id']; ?>"><button>View Reviews</button></a>
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