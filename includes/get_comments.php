<?php
session_start();
include '../db_config/db.php';

$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if (!$post_id) {
  echo json_encode(['comments' => []]);
  exit;
}

// Fetch comments for the post
$query = "SELECT comments.comment_id, comments.comment_text, comments.created_at, users.username
          FROM comments
          JOIN users ON comments.user_id = users.user_id
          WHERE comments.post_id = ?
          ORDER BY comments.created_at DESC";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
  $row['time_ago'] = getTimeAgo($row['created_at']);
  $comments[] = $row;
}

$stmt->close();

echo json_encode(['comments' => $comments]);

/**
 * Calculate time ago from a timestamp
 */
function getTimeAgo($timestamp)
{
  $created = strtotime($timestamp);
  $now = time();
  $diff = $now - $created;

  if ($diff < 60) {
    return "just now";
  } elseif ($diff < 3600) {
    return floor($diff / 60) . "m ago";
  } elseif ($diff < 86400) {
    return floor($diff / 3600) . "h ago";
  } elseif ($diff < 604800) {
    return floor($diff / 86400) . "d ago";
  } else {
    return date("M d, Y", $created);
  }
}
