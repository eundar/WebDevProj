<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <link rel="stylesheet" href="../css/feed.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

  <?php
  include '../includes/get_all_posts.php';

  $query = "SELECT posts.*, users.username, COUNT(likes.like_id) AS total_likes
            FROM posts 
            JOIN users ON posts.author_id = users.user_id 
            LEFT JOIN likes ON posts.post_id = likes.post_id
            GROUP BY posts.post_id
            ORDER BY posts.created_at DESC";

  $all_posts = get_all_posts($connection, $query);

  if (!empty($all_posts)):
    foreach ($all_posts as $post):
  ?>

      <div class="post">
        <div class="post-header">
          <a href="../pages/profile.php?id=<?php echo $post['author_id']; ?>">
            <strong><?php echo htmlspecialchars($post['username']); ?></strong>
          </a>

          <p style="color: white;"><?php echo htmlspecialchars($post['time_ago']); ?> </p>
        </div>

        <div class="post-body">
          <p><?php echo htmlspecialchars($post['caption']); ?></p>
          <?php if (!empty($post['image'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="post image">
          <?php endif; ?>
        </div>

        <div class="post-interaction">
          <!-- Small form for the like action -->
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

</body>

</html>