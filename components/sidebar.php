<nav class="sidebar">
  <ul class="navigation-links">
    <li><a href="../pages/homepage.php"><i class="fas fa-home"></i>
        <p>Home</p>
      </a></li>
    <li>
      <hr>
    </li>
    <li><a href="../pages/profile.php?id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user-circle"></i>
        <p>My Profile</p>
      </a></li>
    <li>
      <hr>
    </li>
    <li><a href="#"><i class="fas fa-cog"></i>
        <p>Settings</p>
      </a></li>
    <li>
      <hr>
    </li>
    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i>
        <p>Logout</p>
      </a></li>
  </ul>
</nav>