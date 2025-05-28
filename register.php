<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username && $password && $password_confirm) {
        if ($password === $password_confirm) {
            $stmt = $conn->prepare("INSERT INTO users (username, password, is_admin, profile_photo) VALUES (?, ?, ?, 'default.jpg')");
            if ($stmt) {
                $is_admin = 0;
                $stmt->bind_param("ssi", $username, $password, $is_admin);
                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = $is_admin;
                    header('Location: index.php');
                    exit;
                } else {
                    $error = "Error inserting user: " . $stmt->error;
                }
            } else {
                $error = "Database error";
            }
        } else {
            $error = "Passwords do not match.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Register</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container">
    <h2>Register</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required />

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required />

        <label for="password_confirm">Confirm Password:</label>
        <input type="password" id="password_confirm" name="password_confirm" required />

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>
</body>
</html>