<?php
session_start();
include '../db_config/db.php';
require_once '../auth/auth_crud/auth_check.php';

$logged_in_user_id = $_SESSION['user_id'];
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
  die("User not found.");
}

/* Get user info */
$stmt = $connection->prepare("SELECT user_id, username, profile_pic, bio FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$user = $result->fetch_assoc()) {
  die("User not found.");
}
$stmt->close();

/* Check if already following */
$checkStmt = $connection->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ?");
$checkStmt->bind_param("ii", $logged_in_user_id, $user_id);
$checkStmt->execute();
$isFollowing = $checkStmt->get_result()->num_rows > 0;
$checkStmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($user['username']); ?> Profile</title>
  <link rel="stylesheet" href="../css/profile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

  <div class="profile-container">

    <div class="profile-header">

      <img class="profile-pic" src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : '../assets/defaultprofile.png'; ?>" alt="Profile Picture">

      <h1><?php echo htmlspecialchars($user['username']); ?></h1>

      <p class="bio">
        <?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?>
      </p>

      <div class="actions">
        <?php if ($logged_in_user_id != $user_id): ?>
          <form action="../includes/follow.php" method="POST">
            <input type="hidden" name="friend_id" value="<?php echo $user_id; ?>">
            <button type="submit" class="follow">
              <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
            </button>
          </form>
        <?php endif; ?>
      </div>

    </div>

    <!-- USER POSTS -->
    <div class="profile-posts">

      <h2>Posts</h2>

      <?php
      include '../includes/get_all_posts.php';

      $query = "SELECT posts.*, users.username, COUNT(likes.like_id) AS total_likes
            FROM posts 
            JOIN users ON posts.author_id = users.user_id 
            LEFT JOIN likes ON posts.post_id = likes.post_id
            WHERE posts.author_id = $user_id
            GROUP BY posts.post_id
            ORDER BY posts.created_at DESC";

      $all_posts = get_all_posts($connection, $query);

      if (!empty($all_posts)):
        foreach ($all_posts as $post):
      ?>

          <div class="post">
            <div class="post-header">
              <strong><?php echo htmlspecialchars($post['username']); ?></strong>
              <p style="color: white;"><?php echo htmlspecialchars($post['time_ago']); ?></p>
            </div>

            <div class="post-body">
              <p><?php echo htmlspecialchars($post['caption']); ?></p>
            </div>

            <div class="post-interaction">
              <form action="../includes/like_post.php" method="POST" class="like-form" style="display:inline;" data-post-id="<?php echo $post['post_id']; ?>">
                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                <button type="submit" class="like-btn">
                  <i class="fas fa-thumbs-up"></i>
                  Like (<span class="like-count"><?php echo $post['total_likes'] ?? 0 ?></span>)
                </button>
              </form>
            </div>
          </div>

        <?php endforeach; ?>

      <?php else: ?>
        <p>No posts yet!</p>
      <?php endif; ?>

    </div>

  </div>

  <script>
    document.addEventListener('submit', function(event) {
      const form = event.target.closest('.like-form');
      if (!form) return;

      event.preventDefault();
      const formData = new FormData(form);

      fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (!data.success) {
            alert(data.message || 'Could not update like count.');
            return;
          }

          const button = form.querySelector('.like-btn');
          const countSpan = button.querySelector('.like-count');
          if (countSpan) {
            countSpan.textContent = data.likes;
          } else {
            button.innerHTML = `<i class="fas fa-thumbs-up"></i> Like (${data.likes})`;
          }
        })
        .catch(error => {
          console.error('Like request failed:', error);
          alert('Unable to update like right now.');
        });
    });
  </script>

</body>

</html>