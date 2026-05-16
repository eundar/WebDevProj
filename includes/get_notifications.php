<?php
include '../db_config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'notifications' => [], 'unread' => 0]);
  exit;
}

$user_id = $_SESSION['user_id'];

// Mark as read if requested
if (isset($_GET['mark_read'])) {
  $mark = $connection->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
  $mark->bind_param("i", $user_id);
  $mark->execute();
  $mark->close();
}

// Fetch notifications
$stmt = $connection->prepare("
  SELECT n.notif_id, n.type, n.post_id, n.is_read, n.created_at,
         u.username, u.profile_pic
  FROM notifications n
  JOIN users u ON n.from_user_id = u.user_id
  WHERE n.user_id = ?
  ORDER BY n.created_at DESC
  LIMIT 20
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
$unread = 0;

while ($row = $result->fetch_assoc()) {
  if (!$row['is_read']) $unread++;

  // Build message
  if ($row['type'] === 'follow') {
    $message = $row['username'] . ' started following you.';
  } elseif ($row['type'] === 'like') {
    $message = $row['username'] . ' liked your post.';
  } elseif ($row['type'] === 'comment') {
    $message = $row['username'] . ' commented on your post.';
  } else {
    $message = $row['username'] . ' interacted with you.';
  }

  // Time ago
  $diff = time() - strtotime($row['created_at']);
  if ($diff >= 86400) $time_ago = floor($diff / 86400) . 'd ago';
  elseif ($diff >= 3600) $time_ago = floor($diff / 3600) . 'h ago';
  elseif ($diff >= 60) $time_ago = floor($diff / 60) . 'm ago';
  else $time_ago = 'Just now';

  $notifications[] = [
    'message'     => $message,
    'time_ago'    => $time_ago,
    'is_read'     => $row['is_read'],
    'profile_pic' => !empty($row['profile_pic']) ? '../uploads/' . $row['profile_pic'] : '../assets/defaultprofile.png',
  ];
}

$stmt->close();

echo json_encode([
  'success'       => true,
  'notifications' => $notifications,
  'unread'        => $unread
]);
?>