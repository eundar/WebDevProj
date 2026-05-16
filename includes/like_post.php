<?php
include '../db_config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
  $post_id = $_POST['post_id'];
  $user_id = $_SESSION['user_id'];
  $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  if (empty($post_id) || !is_numeric($post_id)) {
    if ($is_ajax) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
      exit();
    }
    echo 'Invalid post ID.';
    exit();
  }

  // Check if already liked
  $check_query = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
  $stmt = mysqli_prepare($connection, $check_query);
  mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) == 0) {
    // Insert like
    $insert_query = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($connection, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ii", $post_id, $user_id);
    mysqli_stmt_execute($insert_stmt);

    // Get post owner
    $owner_stmt = $connection->prepare("SELECT author_id FROM posts WHERE post_id = ?");
    $owner_stmt->bind_param("i", $post_id);
    $owner_stmt->execute();
    $owner = $owner_stmt->get_result()->fetch_assoc();
    $owner_stmt->close();

    // Only notify if liking someone else's post
    if ($owner && $owner['author_id'] != $user_id) {
      $notif = $connection->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id) VALUES (?, ?, 'like', ?)");
      $notif->bind_param("iii", $owner['author_id'], $user_id, $post_id);
      $notif->execute();
      $notif->close();
    }
  } else {
    // Unlike
    $delete_query = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
    $delete_stmt = mysqli_prepare($connection, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "ii", $post_id, $user_id);
    mysqli_stmt_execute($delete_stmt);
  }

  // Get updated like count
  $likes_query = "SELECT COUNT(*) AS total_likes FROM likes WHERE post_id = ?";
  $likes_stmt = mysqli_prepare($connection, $likes_query);
  mysqli_stmt_bind_param($likes_stmt, "i", $post_id);
  mysqli_stmt_execute($likes_stmt);
  $likes_result = mysqli_stmt_get_result($likes_stmt);
  $likes_row = mysqli_fetch_assoc($likes_result);
  $total_likes = $likes_row['total_likes'] ?? 0;

  if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'likes' => (int)$total_likes]);
    exit();
  }

  header("Location: ../index.php");
  exit();
}