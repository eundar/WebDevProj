<?php
session_start();
include '../db_config/db.php';
require_once '../auth/auth_crud/auth_check.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
$user_id = $_SESSION['user_id'];

if (!$post_id || !$comment_text) {
  echo json_encode(['success' => false, 'message' => 'Invalid input']);
  exit;
}

// Verify the post exists
$checkPost = $connection->prepare("SELECT post_id FROM posts WHERE post_id = ?");
$checkPost->bind_param("i", $post_id);
$checkPost->execute();
if ($checkPost->get_result()->num_rows === 0) {
  $checkPost->close();
  echo json_encode(['success' => false, 'message' => 'Post not found']);
  exit;
}
$checkPost->close();

// Insert the comment
$stmt = $connection->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $post_id, $user_id, $comment_text);

if ($stmt->execute()) {
  $stmt->close();
  echo json_encode(['success' => true, 'message' => 'Comment posted successfully']);
} else {
  $stmt->close();
  echo json_encode(['success' => false, 'message' => 'Failed to post comment']);
}
