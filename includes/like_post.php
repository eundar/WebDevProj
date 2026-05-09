<?php
include '../db_config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
  $post_id = $_POST['post_id'];
  $user_id = $_SESSION['user_id'];

  // Check if the user already liked this post (to prevent double likes)
  $check_query = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
  $stmt = mysqli_prepare($connection, $check_query);
  mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) == 0) {
    // If not liked yet, insert the like
    $insert_query = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($connection, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ii", $post_id, $user_id);
    mysqli_stmt_execute($insert_stmt);
  } else {
    // If already liked, "unlike" it by deleting the row
    $delete_query = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
    $delete_stmt = mysqli_prepare($connection, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "ii", $post_id, $user_id);
    mysqli_stmt_execute($delete_stmt);
  }

  // Go back to the feed
  header("Location: ../index.php");
  exit();
}
