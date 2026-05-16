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
      <div id="searchResults" class="search-results"></div>
    </div>

    <!-- BELL NOTIFICATION -->
    <div class="notif-wrap" id="notifWrap">
      <i class="fas fa-bell" id="notifBell"></i>
      <span class="notif-badge" id="notifBadge" style="display:none;">0</span>

      <div class="notif-dropdown" id="notifDropdown">
        <div class="notif-header">
          <strong>Notifications</strong>
        </div>
        <div class="notif-list" id="notifList">
          <p class="notif-empty">No notifications yet.</p>
        </div>
      </div>
    </div>

    <a href="../pages/profile.php?id=<?php echo $_SESSION['user_id']; ?>">
      <figure class="userprofile">
        <img src="<?php echo $profile_pic; ?>">
      </figure>
    </a>

  </div>
</header>

<style>
  .notif-wrap {
    position: relative;
    cursor: pointer;
  }

  .notif-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: red;
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .notif-dropdown {
    display: none;
    position: absolute;
    top: 2.5rem;
    right: 0;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    width: 300px;
    z-index: 999;
    overflow: hidden;
  }

  .notif-dropdown.open {
    display: block;
  }

  .notif-header {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #eee;
    color: #333;
    font-size: 0.95rem;
  }

  .notif-list {
    max-height: 320px;
    overflow-y: auto;
  }

  .notif-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.85rem;
    color: #333;
  }

  .notif-item.unread {
    background: #f5f0eb;
  }

  .notif-item img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
  }

  .notif-item-text {
    flex: 1;
  }

  .notif-item-time {
    font-size: 0.75rem;
    color: #aaa;
    margin-top: 0.2rem;
  }

  .notif-empty {
    padding: 1rem;
    text-align: center;
    color: #aaa;
    font-size: 0.85rem;
  }
</style>

<script>
  const notifBell = document.getElementById('notifBell');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifBadge = document.getElementById('notifBadge');
  const notifList = document.getElementById('notifList');

  // Load notifications on page load
  fetchNotifications();

  // Poll every 30 seconds
  setInterval(fetchNotifications, 30000);

  function fetchNotifications() {
    fetch('../includes/get_notifications.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;

        // Update badge
        if (data.unread > 0) {
          notifBadge.textContent = data.unread;
          notifBadge.style.display = 'flex';
        } else {
          notifBadge.style.display = 'none';
        }

        // Update list
        if (data.notifications.length === 0) {
          notifList.innerHTML = '<p class="notif-empty">No notifications yet.</p>';
          return;
        }

        notifList.innerHTML = data.notifications.map(n => `
          <div class="notif-item ${n.is_read ? '' : 'unread'}">
            <img src="${n.profile_pic}" alt="pic">
            <div class="notif-item-text">
              <div>${n.message}</div>
              <div class="notif-item-time">${n.time_ago}</div>
            </div>
          </div>
        `).join('');
      });
  }

  // Toggle dropdown
  notifBell.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle('open');

    // Mark as read when opened
    if (notifDropdown.classList.contains('open')) {
      fetch('../includes/get_notifications.php?mark_read=1')
        .then(() => {
          notifBadge.style.display = 'none';
          // Remove unread highlight
          document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
        });
    }
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#notifWrap')) {
      notifDropdown.classList.remove('open');
    }
  });
</script>