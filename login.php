<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vulnerable SQL Injection
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required />
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required />
        
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>
</body>
</html>