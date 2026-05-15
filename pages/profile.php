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

/* Get logged-in user's profile picture for header */
$headerStmt = $connection->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
$headerStmt->bind_param("i", $logged_in_user_id);
$headerStmt->execute();
$headerResult = $headerStmt->get_result();
$headerUser = $headerResult->fetch_assoc();
$profile_pic = !empty($headerUser['profile_pic']) ? $headerUser['profile_pic'] : '../assets/defaultprofile.png';
$headerStmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($user['username']); ?> Profile</title>
  <link rel="stylesheet" href="../css/profile.css">
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/friendlist.css">
  <link rel="stylesheet" href="../components/modals/show_comments.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<header>
  <?php include '../components/header.php'; ?>
</header>

<body>
  <main>
    <?php
    include '../components/sidebar.php';
    ?>
    <div class="middle">
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

      <h2>Posts</h2>
      <?php include '../components/profilefeed.php'; ?>
    </div>
    <?php
    include '../components/friendlist.php';
    ?>
    
    <!-- Include the comments modal -->
    <?php include '../components/modals/show_comments.php'; ?>
  </main>

  <script>
    const input = document.getElementById("searchInput");
    const resultsBox = document.getElementById("searchResults");

    input.addEventListener("keyup", function() {
      let query = this.value.trim();

      if (query.length === 0) {
        resultsBox.style.display = "none";
        resultsBox.innerHTML = "";
        return;
      }

      fetch("../includes/live_search.php?q=" + encodeURIComponent(query))
        .then(res => res.text())
        .then(data => {
          resultsBox.innerHTML = data;
          resultsBox.style.display = "block";
        });
    });

    document.addEventListener("click", function(e) {
      if (!e.target.closest(".searchbar")) {
        resultsBox.style.display = "none";
      }
    });
    document.addEventListener('submit', function(event) {
      const form = event.target.closest('.like-form');
      if (!form) return

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

    // Comments Modal Functionality
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
    document.addEventListener('click', function(e) {
      if (e.target.closest('.comments-btn')) {
        const btn = e.target.closest('.comments-btn');
        currentPostId = btn.dataset.postId;
        commentsModal.classList.add('active');
        loadComments(currentPostId);
      }
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