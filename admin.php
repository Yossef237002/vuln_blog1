<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['is_admin']) {
    header('Location: index.php');
    exit;
}

// Handle post deletion
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// Handle comment deletion
if (isset($_GET['delete_comment'])) {
    $comment_id = (int)$_GET['delete_comment'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// Fetch all posts and comments
$posts = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.id DESC");
$comments = $conn->query("SELECT comments.*, users.username, posts.title FROM comments JOIN users ON comments.user_id = users.id JOIN posts ON comments.post_id = posts.id ORDER BY comments.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <header>
    <nav>
      <div class="brand">
        <i class="fas fa-blog"></i> Vuln Blog
      </div>
      <div class="nav-links">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Posts</a>
        <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </nav>
  </header>

  <div class="container">
    <div class="admin-panel">
      <h1 class="text-center"><i class="fas fa-cog"></i> Admin Panel</h1>

      <section class="admin-section">
        <h2><i class="fas fa-newspaper"></i> Posts Management</h2>
        <?php if ($posts->num_rows === 0): ?>
          <p>No posts found.</p>
        <?php else: ?>
          <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="admin-item">
              <h3><?php echo htmlspecialchars($post['title']); ?></h3>
              <p><?php echo substr(htmlspecialchars($post['content']), 0, 150); ?>...</p>
              <div class="item-meta">
                <span>By: <?php echo htmlspecialchars($post['username']); ?></span>
                <span>Created: <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
              </div>
              <div class="admin-actions">
                <a href="admin.php?delete_post=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                  <i class="fas fa-trash"></i> Delete Post
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </section>

      <section class="admin-section">
        <h2><i class="fas fa-comments"></i> Comments Management</h2>
        <?php if ($comments->num_rows === 0): ?>
          <p>No comments found.</p>
        <?php else: ?>
          <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="admin-item">
              <p><?php echo substr(htmlspecialchars($comment['comment']), 0, 200); ?>...</p>
              <div class="item-meta">
                <span>By: <?php echo htmlspecialchars($comment['username']); ?></span>
                <span>On: "<?php echo htmlspecialchars($comment['title']); ?>"</span>
              </div>
              <div class="admin-actions">
                <a href="admin.php?delete_comment=<?php echo $comment['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">
                  <i class="fas fa-trash"></i> Delete Comment
                </a>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </section>
    </div>
  </div>
</body>
</html>