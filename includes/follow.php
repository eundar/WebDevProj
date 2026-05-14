<?php
include '../db_config/db.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: ../auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id   = $_SESSION['user_id'];
  $friend_id = intval($_POST['friend_id']);

  // Can't follow yourself
  if ($user_id === $friend_id) {
    header("Location: ../pages/profile.php?id=$friend_id");
    exit;
  }

  // Check if already friends
  $check = $connection->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ?");
  $check->bind_param("ii", $user_id, $friend_id);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows === 0) {
    // Not yet friends, so add
    $stmt = $connection->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
    $stmt->close();
  } else {
    // Already friends, so unfollow
    $stmt = $connection->prepare("DELETE FROM friends WHERE user_id = ? AND friend_id = ?");
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
    $stmt->close();
  }

  $check->close();
  header("Location: ../pages/profile.php?id=$friend_id");
  exit;
}
?>