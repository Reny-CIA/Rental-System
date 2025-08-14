<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout settings
$timeout_duration = 600; // 10 minutes in seconds
if (isset($_SESSION['login_id'])) {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        session_unset();
        session_destroy();
        header("Location: landing.php");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
} else {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'login.php' && $current_page !== 'landing.php') {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: landing.php");
        exit();
    }
}
?>

<style>
    /* Sidebar container */
    nav#sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 220px;
        background-color: #007BFF; /* Blue */
        color: black;
        overflow-y: auto;
        padding-top: 1rem;
    }

    /* Sidebar links */
    nav#sidebar a {
        display: block;
        color: black;
        text-decoration: none;
        padding: 12px 20px;
        transition: background 0.2s;
    }

    /* Hover and active state */
    nav#sidebar a:hover,
    nav#sidebar a.active {
        background-color: #d3d3d3; /* Light gray */
        color: black;
    }

    /* Icon spacing */
    nav#sidebar .icon-field {
        width: 20px;
        display: inline-block;
    }

    /* Logout link */
    .logout-link {
        position: absolute;
        bottom: 20px;
        left: 20px;
        color: #ff4d4d;
        font-weight: bold;
        text-decoration: none;
    }

    .logout-link:hover {
        color: #FFA07A;
        text-decoration: underline;
    }

    /* Push main content right */
    body {
        margin-left: 220px;
    }
</style>

<nav id="sidebar">
    <a href="index.php?page=home" class="nav-home">
        <span class="icon-field"><i class="fa fa-tachometer-alt"></i></span> Dashboard
    </a>
    <a href="index.php?page=categories" class="nav-categories">
        <span class="icon-field"><i class="fa fa-th-list"></i></span> House Type
    </a>
    <a href="index.php?page=houses" class="nav-houses">
        <span class="icon-field"><i class="fa fa-home"></i></span> Houses
    </a>
    <a href="index.php?page=tenants" class="nav-tenants">
        <span class="icon-field"><i class="fa fa-user-friends"></i></span> Tenants
    </a>
    <a href="index.php?page=invoices" class="nav-invoices">
        <span class="icon-field"><i class="fa fa-file-invoice"></i></span> Payments
    </a>
    <a href="index.php?page=reports" class="nav-reports">
        <span class="icon-field"><i class="fa fa-list-alt"></i></span> Reports
    </a>

    <?php if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 1): ?>
        <a href="index.php?page=users" class="nav-users">
            <span class="icon-field"><i class="fa fa-users"></i></span> Users
        </a>
    <?php endif; ?>

    <a href="logout.php" class="logout-link text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</nav>

<!-- Highlight active page -->
<script>
    let currentPage = '<?php echo isset($_GET["page"]) ? $_GET["page"] : "home"; ?>';
    document.querySelectorAll('#sidebar a').forEach(link => {
        if (link.classList.contains('nav-' + currentPage)) {
            link.classList.add('active');
        }
    });
</script>
