<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/root.css">
</head>

<?php
session_start();

require_once '../auth/auth_crud/auth_check.php';

include '../db_config/db.php';
// User is logged in, continue with page
$user_id = $_SESSION['user_id'];

$stmt = $connection->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : '../assets/defaultprofile.png';
$stmt->close();
?>


<header>
  <?php include '../components/header.php'; ?>
</header>

<body>
  <main>
    <?php
    include '../components/sidebar.php';
    ?>

    <div class="middle">
      <?php
      include '../components/createpost.php';
      include '../components/feed.php';
      ?>
    </div>

    <?php
    include '../components/friendlist.php';
    ?>
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
  </script>

</body>

</html>