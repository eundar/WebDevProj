<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
</head>

<?php
include '../includes/get_all_friends.php';
$all_friends = get_friends($connection, $user_id);

if (!empty($all_friends)):
?>

  <body>
    <section class="friend-list-section">
      <p style="margin:1rem;">Following</p>
      <hr>
      <ul class="friend-list">
        <?php foreach ($all_friends as $friend): ?>
          <li>
            <figure class="friend-profile">
              <img src="<?php echo !empty($friend['profile_pic']) ? $friend['profile_pic'] : '../assets/defaultprofile.png'; ?>" alt="Profile Picture">
            </figure>
            <a href="../pages/profile.php?id=<?php echo $friend['user_id']; ?>">
              <p class="friend-name"><?php echo htmlspecialchars($friend['username']); ?></p>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </body>

<?php else: ?>

  <body>
    <section class="friend-list-section">
      <p style="margin:1rem;">Following</p>
      <hr>
      <ul class="friend-list">
        <li>
          <p>You Are Not Following Anyone<br />:< </p>
        </li>
      </ul>
    </section>
  </body>
<?php endif; ?>

</html>