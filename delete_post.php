<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['id'] ?? 0;

// IDOR Vulnerability - No ownership check
$stmt = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

header("Location: index.php");
exit;
?>