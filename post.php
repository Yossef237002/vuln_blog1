<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$is_admin = $_SESSION['is_admin'] ?? false;
$post_id = $_GET['id'] ?? 0;

// Get post details
$post_stmt = $conn->prepare("SELECT posts.id, posts.title, posts.content, users.username, posts.user_id 
                            FROM posts 
                            JOIN users ON posts.user_id = users.id 
                            WHERE posts.id = ?");
if ($post_stmt) {
    $post_stmt->bind_param("i", $post_id);
    $post_stmt->execute();
    $post_result = $post_stmt->get_result();
    $post = $post_result->fetch_assoc();
    $post_stmt->close();
}

if (!$post) die("Post not found");

$is_owner = ($_SESSION['user_id'] == $post['user_id']);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'] ?? '';
    if (!empty(trim($comment))) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $_SESSION['user_id'], $comment);
        $stmt->execute();
        header("Location: post.php?id=$post_id");
        exit;
    }
}

// Get comments
$comments_stmt = $conn->prepare("SELECT comments.id, comments.comment, users.username, comments.user_id 
                                FROM comments 
                                JOIN users ON comments.user_id = users.id 
                                WHERE comments.post_id = ? 
                                ORDER BY comments.id ASC");
if ($comments_stmt) {
    $comments_stmt->bind_param("i", $post_id);
    $comments_stmt->execute();
    $comments_result = $comments_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= $post['title'] ?> - Vuln Blog</title>
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
        <a href="profile.php?id=<?= $_SESSION['user_id'] ?>"><i class="fas fa-user"></i> Profile</a>
        <span>Logged in as <?= $_SESSION['username'] ?></span>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </nav>
  </header>

  <div class="container">
    <article>
      <h1><?= $post['title'] ?></h1> <!-- XSS Vulnerability -->
      <p><?= nl2br($post['content']) ?></p> <!-- XSS Vulnerability -->
      <div class="post-footer">
        <small>By: <a href="profile.php?id=<?= $post['user_id'] ?>"><?= $post['username'] ?></