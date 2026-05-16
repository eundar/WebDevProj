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
  $caption = trim($_POST['caption'] ?? '');

  if (!$post_id || empty($caption)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
  }

  $stmt = $connection->prepare("UPDATE posts SET caption = ? WHERE post_id = ? AND author_id = ?");
  $stmt->bind_param("sii", $caption, $post_id, $user_id);

  if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'caption' => $caption]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Could not update post']);
  }

  $stmt->close();
  exit;
}
?>