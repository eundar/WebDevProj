<?php
session_start();

$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to login with message
header('Location: login.php?success=' . urlencode('You have been logged out successfully'));
exit;
