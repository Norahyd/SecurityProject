<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// VULNERABILITY: Broken Access Control
// Anyone can delete any review, regardless of whether they are admin or the original author
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review_id'])) {
    $review_id = $_POST['delete_review_id'];
    $review_user_id = $_POST['review_user_id'];

    if (true) {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
    }

    header("Location: reviews_vulnerable.php?book_id=$book_id");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);

    $check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0 && $role === 'user') {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $book_id, $content);
        $stmt->execute();
    }

    header("Location: reviews_vulnerable.php?book_id=$book_id");
    exit();
}

// Fetch book and reviews
$book_stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$book_stmt->bind_param("i", $book_id);
$book_stmt->execute();
$book = $book_stmt->get_result()->fetch_assoc();

$review_stmt = $conn->prepare("SELECT reviews.*, users.username FROM reviews JOIN users ON reviews.user_id = users.id WHERE book_id = ?");
$review_stmt->bind_param("i", $book_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reviews - <?php echo $book['title']; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f8; margin: 0; }
        nav { background: #6a5acd; padding: 12px; text-align: center; }
        nav a { color: white; margin: 0 10px; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 20px auto; background: white; padding: 20px; border-radius: 10px; }
        .review { background: #f9f9ff; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ddd; }
        .review strong { color: #4b3ea7; }
        textarea { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 10px; }
        button { background: #6a5acd; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #e8491d; }
    </style>
</head>
<body>
<nav>
    <a href="dashboard_vulnerable.php">Dashboard</a>
    <a href="book_vulnerable.php">Back to Books</a>
</nav>

<div class="container">
    <h2>Reviews for "<?php echo $book['title']; ?>"</h2>

    <?php while ($review = $reviews->fetch_assoc()): ?>
        <div class="review">

            <!-- VULNERABILITY: Reflected XSS -->
            <!-- Username and review content are printed directly without escaping -->
            <!-- An attacker could inject <script> tags in their review -->
            <strong><?php echo $review['username']; ?></strong>
            <p><?php echo nl2br($review['content']); ?></p>

            <!-- VULNERABILITY: Delete button is visible to everyone -->
            <!-- No check for admin or review owner before showing the delete form -->
            <?php if (true): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_review_id" value="<?php echo $review['id']; ?>">
                    <input type="hidden" name="review_user_id" value="<?php echo $review['user_id']; ?>">
                    <button onclick="return confirm('Delete this review?')">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <?php
    $check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0 && $role === 'user'): ?>
        <form method="post">
            <textarea name="content" placeholder="Write your review..." required></textarea>
            <button type="submit">Submit Review</button>
        </form>
    <?php elseif ($role === 'user'): ?>
        <p>You have already reviewed this book.</p>
    <?php endif; ?>
</div>
</body>
</html>