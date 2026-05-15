<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <link rel="stylesheet" href="../css/feed.css">
  <link rel="stylesheet" href="../components/modals/show_comments.css">
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
          <!-- Like button -->
          <form action="../includes/like_post.php" method="POST" class="like-form" style="display:inline;" data-post-id="<?php echo $post['post_id']; ?>">
            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
            <button type="submit" class="like-btn">
              <i class="fas fa-thumbs-up"></i>
              Like (<span class="like-count"><?php echo $post['total_likes'] ?? 0 ?></span>)
            </button>
          </form>

          <!-- Show Comments button -->
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

  <!-- Include the comments modal -->
  <?php include '../components/modals/show_comments.php'; ?>

  <script>
    const commentsModal = document.getElementById('commentsModal');
    const closeBtn = document.querySelector('.close-comments-btn');
    const submitCommentBtn = document.getElementById('submitCommentBtn');
    const commentText = document.getElementById('commentText');
    let currentPostId = null;

    // Close modal when X is clicked
    closeBtn.addEventListener('click', () => {
      commentsModal.classList.remove('active');
      currentPostId = null;
      document.getElementById('commentsList').innerHTML = '';
      commentText.value = '';
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target === commentsModal) {
        commentsModal.classList.remove('active');
        currentPostId = null;
        document.getElementById('commentsList').innerHTML = '';
        commentText.value = '';
      }
    });

    // Open comments modal when button is clicked
    document.querySelectorAll('.comments-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        currentPostId = this.dataset.postId;
        commentsModal.classList.add('active');
        loadComments(currentPostId);
      });
    });

    // Load comments for a post
    function loadComments(postId) {
      document.getElementById('commentsLoader').style.display = 'block';
      document.getElementById('commentsList').innerHTML = '';

      fetch(`../includes/get_comments.php?post_id=${postId}`)
        .then(res => res.json())
        .then(data => {
          document.getElementById('commentsLoader').style.display = 'none';
          const commentsList = document.getElementById('commentsList');

          if (data.comments && data.comments.length > 0) {
            data.comments.forEach(comment => {
              const commentDiv = document.createElement('div');
              commentDiv.className = 'comment';
              commentDiv.innerHTML = `
                <div class="comment-author">${escapeHtml(comment.username)}</div>
                <div class="comment-text">${escapeHtml(comment.comment_text)}</div>
                <div class="comment-time">${comment.time_ago}</div>
              `;
              commentsList.appendChild(commentDiv);
            });
          } else {
            commentsList.innerHTML = '<p style="text-align: center; color: #999;">No comments yet. Be the first!</p>';
          }
        })
        .catch(err => {
          document.getElementById('commentsLoader').style.display = 'none';
          console.error('Error loading comments:', err);
          document.getElementById('commentsList').innerHTML = '<p style="color: red;">Error loading comments</p>';
        });
    }

    // Submit a comment
    submitCommentBtn.addEventListener('click', () => {
      const text = commentText.value.trim();
      if (!text || !currentPostId) return;

      submitCommentBtn.disabled = true;
      submitCommentBtn.textContent = 'Posting...';

      fetch('../includes/add_comment.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `post_id=${currentPostId}&comment_text=${encodeURIComponent(text)}`
        })
        .then(res => res.json())
        .then(data => {
          submitCommentBtn.disabled = false;
          submitCommentBtn.textContent = 'Post Comment';
          if (data.success) {
            commentText.value = '';
            loadComments(currentPostId);
          } else {
            alert('Error posting comment: ' + data.message);
          }
        })
        .catch(err => {
          submitCommentBtn.disabled = false;
          submitCommentBtn.textContent = 'Post Comment';
          console.error('Error posting comment:', err);
        });
    });

    // Utility function to escape HTML
    function escapeHtml(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.replace(/[&<>"']/g, m => map[m]);
    }
  </script>

</body>

</html>