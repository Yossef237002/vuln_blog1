<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    
    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->bind_param("si", $bio, $_SESSION['user_id']);
    $stmt->execute();
}

header("Location: profile.php");
exit;