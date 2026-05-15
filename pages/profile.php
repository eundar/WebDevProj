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

/* Set profile picture with fallback to default */
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : '../assets/defaultprofile.png';
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
  </main>

  <script>
    //livesearch script//////////////////////
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

    document.addEventListener("submit", function(event) {
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
    /////////////////////////////////////////

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
  </script>

</body>

</html>