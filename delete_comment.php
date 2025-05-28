<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if user is owner or admin
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();

if (!$comment) {
    die("Comment not found");
}

// Check if admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($comment['user_id'] == $_SESSION['user_id'] || $user['is_admin']) {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    
    // Redirect back to the post
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: index.php");
    }
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>