<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create error log file
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Starting profile.php\n", FILE_APPEND);

include 'db.php';
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Database included\n", FILE_APPEND);

if (!isset($_SESSION['user_id'])) {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] No session, redirecting to login\n", FILE_APPEND);
    header('Location: login.php');
    exit;
}

// Get profile ID from URL or use current user
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$is_current_user = ($profile_id == $_SESSION['user_id']);
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Profile ID: $profile_id, Current User: {$_SESSION['user_id']}\n", FILE_APPEND);

// Get user profile
$user = null;
$stmt = $conn->prepare("SELECT id, username, profile_photo, bio, created_at FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $profile_id);
    if ($stmt->execute()) {
        $user_result = $stmt->get_result();
        $user = $user_result->fetch_assoc();
        file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] User query successful\n", FILE_APPEND);
    } else {
        file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Execute failed: " . $stmt->error . "\n", FILE_APPEND);
    }
    $stmt->close();
} else {
    $error = "Prepare failed: " . $conn->error;
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] $error\n", FILE_APPEND);
    die($error);
}

if (!$user) {
    $error = "User not found";
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] $error\n", FILE_APPEND);
    die($error);
}

// Get user's posts
$posts_result = null;
$posts_stmt = $conn->prepare("SELECT id, title, content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC");
if ($posts_stmt) {
    $posts_stmt->bind_param("i", $profile_id);
    if ($posts_stmt->execute()) {
        $posts_result = $posts_stmt->get_result();
        file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Posts query successful\n", FILE_APPEND);
    } else {
        file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Posts execute failed: " . $posts_stmt->error . "\n", FILE_APPEND);
    }
} else {
    $error = "Posts prepare failed: " . $conn->error;
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] $error\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-photo-placeholder {
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, #4e54c8, #8f94fb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
  <header>
    <nav>
      <div class="brand">
        <i class="fas fa-blog"></i> Vuln Blog
      </div>
      <div class="nav-links">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Posts</a>
        <a href="profile.php?id=<?= $_SESSION['user_id'] ?>"><i class="fas fa-user"></i> My Profile</a>
        <span>Logged in as <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </nav>
  </header>

  <div class="container profile-container">
    <div class="profile-header">
      <div class="profile-photo">
        <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
          <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="<?= htmlspecialchars($user['username']) ?>">
        <?php else: ?>
          <div class="profile-photo-placeholder">
            <i class="fas fa-user"></i>
          </div>
        <?php endif; ?>
        
        <?php if ($is_current_user): ?>
          <form action="upload_photo.php" method="post" enctype="multipart/form-data" class="upload-form">
            <input type="file" name="profile_photo" id="profile-photo-input" accept="image/*">
            <label for="profile-photo-input" class="btn btn-upload">
              <i class="fas fa-camera"></i> Change Photo
            </label>
            <button type="submit" class="btn btn-primary">Upload</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="profile-info">
        <h1><?= htmlspecialchars($user['username']) ?></h1>
        <p class="member-since">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
        
        <?php if (!empty($user['bio'])): ?>
          <div class="bio">
            <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
          </div>
        <?php else: ?>
          <div class="bio">
            <p><em>No bio yet. <?= $is_current_user ? 'Add one below!' : '' ?></em></p>
          </div>
        <?php endif; ?>
        
        <?php if ($is_current_user): ?>
          <button id="edit-bio-btn" class="btn btn-accent">
            <i class="fas fa-edit"></i> Edit Bio
          </button>
          
          <form id="bio-form" method="post" action="update_bio.php" style="display:none;">
            <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" id="cancel-bio" class="btn btn-secondary">Cancel</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <section class="user-posts">
      <h2><i class="fas fa-newspaper"></i> <?= $is_current_user ? 'Your Posts' : htmlspecialchars($user['username']) . "'s Posts" ?></h2>
      
      <?php if ($posts_result && $posts_result->num_rows === 0): ?>
        <div class="no-posts">
          <i class="fas fa-file-alt"></i>
          <p>No posts yet</p>
          <?php if ($is_current_user): ?>
            <a href="add_post.php" class="btn btn-primary">Create Your First Post</a>
          <?php endif; ?>
        </div>
      <?php elseif ($posts_result): ?>
        <div class="post-grid">
          <?php while ($post = $posts_result->fetch_assoc()): ?>
            <div class="post-card">
              <h3><?= htmlspecialchars($post['title']) ?></h3>
              <p><?= substr(htmlspecialchars($post['content']), 0, 150) ?>...</p>
              <div class="post-meta">
                <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-sm">Read More</a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="error">
          <i class="fas fa-exclamation-triangle"></i>
          <p>Could not load posts. Please try again later.</p>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <script>
    // Show/hide bio editor
    const editBioBtn = document.getElementById('edit-bio-btn');
    const bioForm = document.getElementById('bio-form');
    const cancelBio = document.getElementById('cancel-bio');
    
    if (editBioBtn && bioForm) {
      editBioBtn.addEventListener('click', () => {
        bioForm.style.display = 'block';
        editBioBtn.style.display = 'none';
      });
    }
    
    if (cancelBio && bioForm) {
      cancelBio.addEventListener('click', () => {
        bioForm.style.display = 'none';
        editBioBtn.style.display = 'block';
      });
    }
  </script>
</body>
</html>