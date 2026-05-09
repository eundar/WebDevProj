<?php

function get_all_posts($connection, $query)
{

  $result = mysqli_query($connection, $query);

  $posts = [];

  function timeAgo($datetime)
  {
    $date = new DateTime($datetime);
    $timestamp = $date->getTimestamp();
    $diff = time() - $timestamp;

    if ($diff >= 86400) {
      return floor($diff / 86400) . 'd ago';
    }
    if ($diff >= 3600) {
      return floor($diff / 3600) . 'h ago';
    }
    if ($diff >= 60) {
      return floor($diff / 60) . 'm ago';
    } else {
      return 'Just now';
    }
  }

  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

      if (isset($row['created_at'])) {
        $row['time_ago'] = timeAgo($row['created_at']);
      }

      $posts[] = $row;
    }
  }
  return $posts;
}
