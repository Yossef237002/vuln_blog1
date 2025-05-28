<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title && $content) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $title, $content);
        $stmt->execute();
        header("Location: index.php");
        exit;
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Post - Vuln Blog</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <nav>
      <a href="index.php">Back to Posts</a> |
      Logged in as <?php echo htmlspecialchars($_SESSION['username']); ?> |
      <a href="logout.php">Logout</a>
    </nav>

    <h1>Add New Post</h1>

    <?php if (!empty($error)) : ?>
      <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" action="add_post.php">
      <label for="title">Title</label>
      <input type="text" name="title" id="title" required />

      <label for="content">Content</label>
      <textarea name="content" id="content" rows="6" required></textarea>

      <button type="submit">Create Post</button>
    </form>
  </div>
</body>
</html>
