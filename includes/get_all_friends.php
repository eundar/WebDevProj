<?php
$user_id = $_SESSION['user_id'];

function get_friends($connection, $user_id)
{
  // We look for 'friend_id' where 'user_id' is ME
  // We JOIN with the users table to get their actual names
  $query = "SELECT users.username 
            FROM friends 
            JOIN users ON friends.friend_id = users.user_id 
            WHERE friends.user_id = ?";

  $stmt = mysqli_prepare($connection, $query);

  if (!$stmt) return false;

  mysqli_stmt_bind_param($stmt, "i", $user_id);

  if (!mysqli_stmt_execute($stmt)) return false;

  $result = mysqli_stmt_get_result($stmt);
  if (!$result) return false;

  $friends_list = [];

  while ($row = mysqli_fetch_assoc($result)) {
    // This collects all the usernames into an array
    $friends_list[] = $row['username'];
  }

  mysqli_stmt_close($stmt);
  return $friends_list;
}
