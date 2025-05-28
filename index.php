<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$is_admin = $_SESSION['is_admin'] ?? false;

// Vulnerable search
$search = $_GET['search'] ?? '';

// Fetch posts
$sql = "SELECT posts.id, posts.title, posts.content, users.username, posts.user_id 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Vuln Blog</title>
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
        <span>Welcome, <?= $_SESSION['username'] ?></span>
        <a href="profile.php?id=<?= $_SESSION['user_id'] ?>"><i class="fas fa-user"></i> Profile</a>
        <?php if ($is_admin): ?>
          <a href="admin.php"><i class="fas fa-cog"></i> Admin</a>
        <?php endif; ?>
        <a href="add_post.php"><i class="fas fa-plus"></i> Add Post</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </nav>
  </header>

  <div class="container">
    <h1 class="text-center">All Posts</h1>
    
    <!-- Vulnerable search form -->
    <div class="search-box">
      <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Search posts..." value="<?= $search ?>">
        <button type="submit">Search</button>
      </form>
      <?php if ($search): ?>
        <div class="search-results">Results for: <?= $search ?></div>
      <?php endif; ?>
    </div>

    <?php if ($result->num_rows === 0): ?>
      <div class="text-center">
        <p>No posts yet. Be the first to <a href="add_post.php">create one</a>!</p>
      </div>
    <?php else: ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <article>
          <h2><?= $row['title'] ?></h2> <!-- XSS Vulnerability -->
          <p><?= nl2br($row['content']) ?></p> <!-- XSS Vulnerability -->
          <div class="post-footer">
            <small>By: <a href="profile.php?id=<?= $row['user_id'] ?>"><?= $row['username'] ?></a></small>
            <div class="post-actions">
              <a href="post.php?id=<?= $row['id'] ?>" class="btn"><i class="fas fa-comment"></i> View / Comment</a>
              <?php if ($_SESSION['user_id'] == $row['user_id'] || $is_admin): ?>
                <a href="delete_post.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                  <i class="fas fa-trash"></i> Delete
                </a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</body>
</html>