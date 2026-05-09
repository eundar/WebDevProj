<?php
session_start();
include '../db_config/db.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
  die("User not found.");
}

/* Get user info */
$stmt = $connection->prepare("SELECT user_id, username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

$stmt->execute();
$result = $stmt->get_result();

if (!$user = $result->fetch_assoc()) {
  die("User not found.");
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($user['username']); ?> Profile</title>
  <link rel="stylesheet" href="../css/profile.css">
</head>

<body>

  <div class="profile-container">

    <div class="profile-header">

      <img class="profile-pic" src="">

      <h1><?php echo htmlspecialchars($user['username']); ?></h1>

      <p class="bio">
        <?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?>
      </p>

      <div class="actions">
        <button class="follow">
          Follow
        </button>
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

              <p style="color: white;"><?php echo htmlspecialchars($post['time_ago']); ?> </p>
            </div>

            <div class="post-body">
              <p>
                <?php echo htmlspecialchars($post['caption']); ?>
              </p>
              <img>

              </img>
            </div>

            <div class="post-interaction">
              <!-- Small form for the like action -->
              <form action="../includes/like_post.php" method="POST" style="display:inline;">
                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                <button type="submit" class="like-btn">
                  <i class="fas fa-thumbs-up"></i>
                  Like (<?php echo $post['total_likes'] ?? 0 ?>)
                </button>
              </form>
            </div>
          </div>

        <?php endforeach; ?>

      <?php else: ?>
        <p>No posts yet!</p>
      <?php endif; ?>

</body>

</html>