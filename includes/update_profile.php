<?php
include '../db_config/db.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: ../auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id  = $_SESSION['user_id'];
  $username = trim($_POST['username'] ?? '');
  $bio      = trim($_POST['bio'] ?? '');

  if (empty($username)) {
    header("Location: ../pages/profile.php?id=$user_id&error=Username+cannot+be+empty");
    exit;
  }

  if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
      header("Location: ../pages/profile.php?id=$user_id&error=Invalid+file+type");
      exit;
    }

    $pic_name   = uniqid('pic_') . '.' . $ext;
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $pic_name);

    $stmt = $connection->prepare("UPDATE users SET username = ?, bio = ?, profile_pic = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $username, $bio, $pic_name, $user_id);
  } else {
    $stmt = $connection->prepare("UPDATE users SET username = ?, bio = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $username, $bio, $user_id);
  }

  if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    header("Location: ../pages/profile.php?id=$user_id");
  } else {
    header("Location: ../pages/profile.php?id=$user_id&error=Update+failed");
  }

  $stmt->close();
  exit;
}
?>