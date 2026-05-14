<?php
include '../db_config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content   = trim($_POST['postContent'] ?? '');
  $author_id = $_SESSION['user_id'];
  $image_name = NULL;

  if ($content === '') {
    echo "Cannot submit empty post.";
    exit();
  }

  // Handle image upload if one was provided
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
      echo "Invalid file type.";
      exit();
    }

    // Create unique filename
    $image_name = uniqid('img_') . '.' . $ext;
    $upload_dir = '../uploads/';

    // Create folder if it doesn't exist
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
  }

  $query = "INSERT INTO posts (caption, author_id, image, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
  $stmt = mysqli_prepare($connection, $query);
  mysqli_stmt_bind_param($stmt, "sss", $content, $author_id, $image_name);

  if (!mysqli_stmt_execute($stmt)) {
    echo "Database Error: " . mysqli_stmt_error($stmt);
    exit();
  }

  mysqli_stmt_close($stmt);
  header("Location: ../index.php");
  exit();
}
?>