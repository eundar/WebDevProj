<?php
include '../../db_config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (empty($email) || empty($password)) {
    header('Location: ../login.php?error=' . urlencode('Please fill in all fields'));
    exit;
  }

  $sql = "SELECT user_id, email, password FROM users WHERE email = ?";
  $stmt = mysqli_prepare($connection, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
      // Verify password
      if (password_verify($password, $row['password'])) {
        // Login successful - set session variables
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['logged_in'] = true;

        mysqli_stmt_close($stmt);
        mysqli_close($connection);

        // Redirect to index page
        header('Location: ../../index.php?success=' . urlencode('Login successful! Welcome back, ' . $row['username']));
        exit;
      } else {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        header('Location: ../login.php?error=' . urlencode('Invalid email or password'));
        exit;
      }
    } else {
      mysqli_stmt_close($stmt);
      mysqli_close($connection);
      header('Location: ../login.php?error=' . urlencode('Invalid email or password'));
      exit;
    }
  } else {
    mysqli_close($connection);
    header('Location: ../login.php?error=' . urlencode('Database error'));
    exit;
  }
} else {
  header('Location: ../login.php?error=' . urlencode('Invalid request method'));
  exit;
}
