<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Talkspace Sign Up</title>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Lora:wght@400;600&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/login.css" />
</head>

<body>
  <header>
    <div class="logo-icon"></div>
    <h1>Talkspace</h1>
  </header>

  <main>
    <div class="login-card">

      <div class="left-panel">
        <div class="illustration">
          <svg viewBox="0 0 220 200" xmlns="http://www.w3.org/2000/svg" fill="none">
            <rect x="88" y="18" width="90" height="115" rx="10" fill="#c8a272" transform="rotate(8 88 18)" />
            <path d="M130 65 c-2-5 -9-5 -9 0 c0 4 9 11 9 11 s9-7 9-11 c0-5 -7-5 -9 0z" fill="#fff" opacity=".7" />
            <rect x="135" y="10" width="68" height="45" rx="10" fill="#a99070" />
            <circle cx="150" cy="33" r="4" fill="#fff" opacity=".6" />
            <circle cx="163" cy="33" r="4" fill="#fff" opacity=".6" />
            <circle cx="176" cy="33" r="4" fill="#fff" opacity=".6" />
            <polygon points="145,55 145,65 155,55" fill="#a99070" />
            <rect x="12" y="30" width="95" height="72" rx="12" stroke="#3a2d1e" stroke-width="3" fill="#e8e4db" />
            <rect x="66" y="18" width="10" height="36" rx="3" fill="#5c3d1e" transform="rotate(30 66 18)" />
            <polygon points="66,18 70,8 74,18" fill="#c8a272" transform="rotate(30 66 18)" />
            <line x1="26" y1="56" x2="88" y2="56" stroke="#b89b72" stroke-width="2.5" stroke-linecap="round" />
            <line x1="26" y1="68" x2="78" y2="68" stroke="#b89b72" stroke-width="2.5" stroke-linecap="round" />
            <line x1="26" y1="80" x2="84" y2="80" stroke="#b89b72" stroke-width="2.5" stroke-linecap="round" />
            <polygon points="24,102 24,116 36,102" fill="#e8e4db" stroke="#3a2d1e" stroke-width="3" />
            <rect x="45" y="90" width="88" height="108" rx="10" fill="#f2ede5" stroke="#3a2d1e" stroke-width="2.5" />
            <circle cx="89" cy="120" r="14" stroke="#5c3d1e" stroke-width="2.5" />
            <path d="M68 150 q21-14 42 0" stroke="#5c3d1e" stroke-width="2.5" stroke-linecap="round" />
            <line x1="58" y1="165" x2="120" y2="165" stroke="#b89b72" stroke-width="2" stroke-linecap="round" />
            <line x1="58" y1="175" x2="108" y2="175" stroke="#b89b72" stroke-width="2" stroke-linecap="round" />
            <rect x="50" y="145" width="88" height="42" rx="10" fill="#b89b72" opacity=".3" />
          </svg>
        </div>
        <p class="tagline">Join the Brew,<br>Start Sharing</p>
      </div>

      <div class="right-panel">
        <h2>Create a <strong>Talkspace</strong> account</h2>

        <!-- 1. Added form tag with action pointing to registration.php -->
        <form action="auth_crud/registration_process.php" method="POST">
          <div class="form-group">
            <div class="form-row">
              <!-- 2. Added 'name' attributes to every input -->
              <input type="text" name="first_name" placeholder="First name" required />
              <input type="text" name="last_name" placeholder="Last name" required />
            </div>
            <input type="email" name="email" placeholder="Email address" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="password" name="confirm_password" placeholder="Confirm password" required />
          </div>

          <!-- 3. Changed button to type="submit" -->
          <button type="submit" class="btn-login">Create Account</button>
        </form>

        <a href="./login.php" class="forgot">Already have an account? Log in</a>
      </div>

    </div>
  </main>
</body>

</html>