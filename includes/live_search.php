<?php
include '../db_config/db.php';

$search = $_GET['q'] ?? '';

if (!empty($search)) {

  $stmt = $connection->prepare("
    SELECT user_id, username 
    FROM users 
    WHERE username LIKE ?
  ");

  $like = "%$search%";
  $stmt->bind_param("s", $like);

  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

      echo "<a href='../pages/profile.php?id={$row['user_id']}' class='search-item'>
        " . htmlspecialchars($row['username']) . "
      </a>";
    }
  } else {
    echo "<div class='search-item'>No users found</div>";
  }

  $stmt->close();
}
