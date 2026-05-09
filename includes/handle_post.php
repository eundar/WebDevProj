<?php
include '../db_config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['postContent'] ?? '');

  // IMPORTANT: Use the user_id from the session, not the username
  $author_id = $_SESSION['user_id'];

  if ($content === '') {
    echo "Cannot submit empty post.";
    exit();
  }

  // Safety check to ensure the user is actually logged in
  if (empty($connection) || empty($author_id)) {
    echo "Error: You must be logged in to post.";
    exit();
  }

  function create_post($connection, $author_id, $content)
  {
    // Updated query to use 'caption' and 'author_id'
    $query = "INSERT INTO posts (caption, author_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)";
    $stmt = mysqli_prepare($connection, $query);

    if (!$stmt) return mysqli_error($connection);

    // "si" means: first param is a String (caption), second is an Integer (author_id)
    mysqli_stmt_bind_param($stmt, "si", $content, $author_id);

    if (!mysqli_stmt_execute($stmt)) return mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);
    return true;
  }

  $result = create_post($connection, $author_id, $content);

  if ($result !== true) {
    echo "Database Error: " . $result;
    exit();
  }

  header("Location: ../index.php");
  exit();
}
