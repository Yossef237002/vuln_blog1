<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profile_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt->bind_param("si", $destination, $_SESSION['user_id']);
            $stmt->execute();
            header("Location: profile.php");
            exit;
        }
    }
}

header("Location: profile.php");
exit;