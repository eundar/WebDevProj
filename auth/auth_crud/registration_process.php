<?php
include '../../db_config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1. Get the data from the form
  $first_name = trim($_POST['first_name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  // 2. Quick Validation
  if ($password !== $confirm) {
    die("Passwords do not match. Please go back.");
  }

  // 3. Check if Email already exists
  $check_email = mysqli_prepare($connection, "SELECT email FROM users WHERE email = ?");
  mysqli_stmt_bind_param($check_email, "s", $email);
  mysqli_stmt_execute($check_email);
  mysqli_stmt_store_result($check_email);

  if (mysqli_stmt_num_rows($check_email) > 0) {
    die("This email is already registered.");
  }
  mysqli_stmt_close($check_email);

  // 4. Create a unique username and hash the password
  $username = ucfirst($first_name) . " " . ucfirst($last_name);
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // 5. Insert into the Database
  $query = "INSERT INTO users (email, password, username) VALUES (?, ?, ?)";
  $stmt = mysqli_prepare($connection, $query);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $email, $hashed_password, $username);

    if (mysqli_stmt_execute($stmt)) {
      // Redirect to login page on success
      header("Location: ../login.php?msg=success");
      exit();
    } else {
      echo "Error saving user: " . mysqli_error($connection);
    }
  }
  mysqli_stmt_close($stmt);
}
