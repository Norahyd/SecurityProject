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

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// DELETE REVIEW: Allow if user is admin or review owner
// Prevents unauthorized deletion; validates ownership or role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review_id'])) {
    $review_id = $_POST['delete_review_id'];
    $review_user_id = $_POST['review_user_id'];

    if ($role === 'admin' || $user_id == $review_user_id) {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
    }
    header("Location: reviews.php?book_id=$book_id");
    exit();
}

// ADD REVIEW: allow users to review once per book
// Prevents review spamming and ensures accountability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);

    // Check if the user has already reviewed this book
    $check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0 && $role === 'user') {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $book_id, $content);
        $stmt->execute();
    }
    header("Location: reviews.php?book_id=$book_id");
    exit();
}

// Fetch book information securely
$book_stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$book_stmt->bind_param("i", $book_id);
$book_stmt->execute();
$book = $book_stmt->get_result()->fetch_assoc();

// Fetch all reviews for the selected book
$review_stmt = $conn->prepare("SELECT reviews.*, users.username FROM reviews JOIN users ON reviews.user_id = users.id WHERE book_id = ?");
$review_stmt->bind_param("i", $book_id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reviews - <?php echo htmlspecialchars($book['title']); ?></title>
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
    <a href="dashboard.php">Dashboard</a>
    <a href="books.php">Back to Books</a>
</nav>

<div class="container">
    <h2>Reviews for "<?php echo htmlspecialchars($book['title']); ?>"</h2>

    <?php while ($review = $reviews->fetch_assoc()): ?>
        <div class="review">
            <!-- Prevent XSS with htmlspecialchars -->
            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
            <p><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>

            <!-- Only admins or review owners can delete -->
            <?php if ($role === 'admin' || $user_id == $review['user_id']): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_review_id" value="<?php echo $review['id']; ?>">
                    <input type="hidden" name="review_user_id" value="<?php echo $review['user_id']; ?>">
                    <button onclick="return confirm('Delete this review?')">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <?php
    // Allow submitting review only if not already submitted
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