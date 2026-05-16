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
$profile_pic = !empty($headerUser['profile_pic']) ? '../uploads/' . $headerUser['profile_pic'] : '../assets/defaultprofile.png';
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
    <?php include '../components/sidebar.php'; ?>

    <div class="middle">
      <div class="profile-header">

        <img class="profile-pic" src="<?php echo !empty($user['profile_pic']) ? '../uploads/' . htmlspecialchars($user['profile_pic']) : '../assets/defaultprofile.png'; ?>" alt="Profile Picture">

        <h1><?php echo htmlspecialchars($user['username']); ?></h1>

        <p class="bio">
          <?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?>
        </p>

        <div class="actions">
          <?php if ($logged_in_user_id == $user_id): ?>
            <button class="edit-profile-btn" id="openEditProfile">
              <i class="fas fa-pen"></i> Edit Profile
            </button>
          <?php else: ?>
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

    <?php include '../components/friendlist.php'; ?>
    <?php include '../components/modals/show_comments.php'; ?>
  </main>

  <!-- EDIT PROFILE MODAL -->
  <div class="modal-overlay" id="editProfileModal">
    <div class="modal-box">
      <button class="close-modal-btn" id="closeEditProfile">&times;</button>
      <h3>Edit Profile</h3>
      <form action="../includes/update_profile.php" method="POST" enctype="multipart/form-data">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label>Bio</label>
        <textarea name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

        <label>Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/*">

        <div class="modal-actions">
          <button type="button" class="btn-cancel-modal" id="cancelEditProfile">Cancel</button>
          <button type="submit" class="btn-save-modal">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EDIT POST MODAL -->
  <div class="modal-overlay" id="editPostModal">
    <div class="modal-box">
      <button class="close-modal-btn" id="closeEditPost">&times;</button>
      <h3>Edit Post</h3>
      <label>Caption</label>
      <textarea id="editPostCaption" rows="4"></textarea>
      <input type="hidden" id="editPostId">
      <div class="modal-actions">
        <button type="button" class="btn-cancel-modal" id="cancelEditPost">Cancel</button>
        <button type="button" class="btn-save-modal" id="saveEditPost">Save</button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

  <script>
    // TOAST
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 3000);
    }

    // EDIT PROFILE MODAL
    const editProfileModal = document.getElementById('editProfileModal');
    document.getElementById('openEditProfile')?.addEventListener('click', () => {
      editProfileModal.classList.add('active');
    });
    document.getElementById('closeEditProfile').addEventListener('click', () => {
      editProfileModal.classList.remove('active');
    });
    document.getElementById('cancelEditProfile').addEventListener('click', () => {
      editProfileModal.classList.remove('active');
    });
    editProfileModal.addEventListener('click', (e) => {
      if (e.target === editProfileModal) editProfileModal.classList.remove('active');
    });

    // EDIT POST MODAL
    const editPostModal = document.getElementById('editPostModal');
    document.addEventListener('click', function(e) {
      if (e.target.closest('.edit-post-btn')) {
        const btn = e.target.closest('.edit-post-btn');
        document.getElementById('editPostId').value = btn.dataset.postId;
        document.getElementById('editPostCaption').value = btn.dataset.caption;
        editPostModal.classList.add('active');
      }
    });
    document.getElementById('closeEditPost').addEventListener('click', () => {
      editPostModal.classList.remove('active');
    });
    document.getElementById('cancelEditPost').addEventListener('click', () => {
      editPostModal.classList.remove('active');
    });
    editPostModal.addEventListener('click', (e) => {
      if (e.target === editPostModal) editPostModal.classList.remove('active');
    });

    document.getElementById('saveEditPost').addEventListener('click', () => {
      const postId  = document.getElementById('editPostId').value;
      const caption = document.getElementById('editPostCaption').value.trim();
      if (!caption) return;

      fetch('../includes/update_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&caption=${encodeURIComponent(caption)}`
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.querySelector('#post-' + postId + ' .post-caption').textContent = data.caption;
          editPostModal.classList.remove('active');
          showToast('Post updated!');
        } else {
          showToast(data.message || 'Could not update post.');
        }
      });
    });

    // DELETE POST
    document.addEventListener('click', function(e) {
      if (e.target.closest('.delete-post-btn')) {
        const btn = e.target.closest('.delete-post-btn');
        const postId = btn.dataset.postId;
        if (!confirm('Delete this post?')) return;

        fetch('../includes/delete_post.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `post_id=${postId}`
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const postEl = document.getElementById('post-' + postId);
            postEl.style.transition = 'opacity 0.3s';
            postEl.style.opacity = '0';
            setTimeout(() => postEl.remove(), 300);
            showToast('Post deleted.');
          } else {
            showToast(data.message || 'Could not delete post.');
          }
        });
      }
    });

    // SEARCH
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

    // LIKES
    document.addEventListener('submit', function(event) {
      const form = event.target.closest('.like-form');
      if (!form) return;
      event.preventDefault();
      const formData = new FormData(form);
      fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) return;
        const button = form.querySelector('.like-btn');
        const countSpan = button.querySelector('.like-count');
        if (countSpan) countSpan.textContent = data.likes;
      });
    });

    // COMMENTS
    const commentsModal = document.getElementById('commentsModal');
    const closeBtn = document.querySelector('.close-comments-btn');
    const submitCommentBtn = document.getElementById('submitCommentBtn');
    const commentText = document.getElementById('commentText');
    let currentPostId = null;

    closeBtn.addEventListener('click', () => {
      commentsModal.classList.remove('active');
      currentPostId = null;
      document.getElementById('commentsList').innerHTML = '';
      commentText.value = '';
    });
    window.addEventListener('click', (e) => {
      if (e.target === commentsModal) {
        commentsModal.classList.remove('active');
        currentPostId = null;
        document.getElementById('commentsList').innerHTML = '';
        commentText.value = '';
      }
    });
    document.addEventListener('click', function(e) {
      if (e.target.closest('.comments-btn')) {
        const btn = e.target.closest('.comments-btn');
        currentPostId = btn.dataset.postId;
        commentsModal.classList.add('active');
        loadComments(currentPostId);
      }
    });

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
            commentsList.innerHTML = '<p style="text-align:center;color:#999;">No comments yet. Be the first!</p>';
          }
        });
    }

    submitCommentBtn.addEventListener('click', () => {
      const text = commentText.value.trim();
      if (!text || !currentPostId) return;
      submitCommentBtn.disabled = true;
      submitCommentBtn.textContent = 'Posting...';
      fetch('../includes/add_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${currentPostId}&comment_text=${encodeURIComponent(text)}`
      })
      .then(res => res.json())
      .then(data => {
        submitCommentBtn.disabled = false;
        submitCommentBtn.textContent = 'Post Comment';
        if (data.success) {
          commentText.value = '';
          loadComments(currentPostId);
        }
      });
    });

    function escapeHtml(text) {
      const map = { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' };
      return text.replace(/[&<>"']/g, m => map[m]);
    }
  </script>

</body>
</html>