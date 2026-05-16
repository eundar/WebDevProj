<?php
include '../db_config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $post_id = intval($_POST['post_id'] ?? 0);

  if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post']);
    exit;
  }

  $stmt = $connection->prepare("DELETE FROM posts WHERE post_id = ? AND author_id = ?");
  $stmt->bind_param("ii", $post_id, $user_id);

  if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Could not delete post']);
  }

  $stmt->close();
  exit;
}
?>