<?php
include __DIR__ . '/../includes/get_all_posts.php';

$user_id = (int) $user_id;
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

    <div class="post" id="post-<?php echo $post['post_id']; ?>">
      <div class="post-header">
        <a href="../pages/profile.php?id=<?php echo $post['author_id']; ?>">
          <strong><?php echo htmlspecialchars($post['username']); ?></strong>
        </a>
        <p style="color: white;"><?php echo htmlspecialchars($post['time_ago']); ?></p>

        <?php if ($logged_in_user_id == $user_id): ?>
          <div class="post-actions">
            <button class="edit-post-btn"
              data-post-id="<?php echo $post['post_id']; ?>"
              data-caption="<?php echo htmlspecialchars($post['caption']); ?>">
              <i class="fas fa-pen"></i>
            </button>
            <button class="delete-post-btn" data-post-id="<?php echo $post['post_id']; ?>">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        <?php endif; ?>
      </div>

      <div class="post-body">
        <p class="post-caption"><?php echo htmlspecialchars($post['caption']); ?></p>
        <?php if (!empty($post['image'])): ?>
          <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="post image">
        <?php endif; ?>
      </div>

      <div class="post-interaction">
        <form action="../includes/like_post.php" method="POST" class="like-form" style="display:inline;" data-post-id="<?php echo $post['post_id']; ?>">
          <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
          <button type="submit" class="like-btn">
            <i class="fas fa-thumbs-up"></i>
            Like (<span class="like-count"><?php echo $post['total_likes'] ?? 0 ?></span>)
          </button>
        </form>

        <button class="comments-btn" data-post-id="<?php echo $post['post_id']; ?>">
          <i class="fas fa-comments"></i>
          Comments
        </button>
      </div>
    </div>

  <?php endforeach; ?>

<?php else: ?>
  <p>No posts yet!</p>
<?php endif; ?>