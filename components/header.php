<header class="page-header">
  <div class="leftside">
    <figure class="logo"></figure>
    <h1>Talkspace</h1>
  </div>

  <div class="rightside">

    <div class="searchbar">
      <input type="text" id="searchInput" placeholder="Search username">

      <button type="button">
        <i class="fas fa-search"></i>
      </button>

      <!-- dropdown results -->
      <div id="searchResults" class="search-results"></div>
    </div>

    <i class="fas fa-bell"></i>

    <a href="../pages/profile.php?id=<?php echo $_SESSION['user_id']; ?>">
      <figure class="userprofile">
        <img src="<?php echo $profile_pic; ?>">
      </figure>
    </a>

  </div>
</header>