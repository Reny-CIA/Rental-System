<?php
session_start();

// Optional: Store current page if you want post-login return
if (!empty($_SERVER['REQUEST_URI'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
}

// Kill session
session_unset();
session_destroy();

// Go to landing page
header("Location: landing.php");
exit();
