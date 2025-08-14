
<style>
	.collapse a{
		text-indent:10px;
	}
	nav#sidebar{
		/*background: url(assets/uploads/<?php echo $_SESSION['system']['cover_img'] ?>) !important*/
	}
</style>

<nav id="sidebar" class='mx-lt-5 bg-dark' >
		
		<div class="sidebar-list">
				<a href="index.php?page=home" class="nav-item nav-home"><span class='icon-field'><i class="fa fa-tachometer-alt "></i></span> Dashboard</a>
				<a href="index.php?page=categories" class="nav-item nav-categories"><span class='icon-field'><i class="fa fa-th-list "></i></span> House Type</a>
				<a href="index.php?page=houses" class="nav-item nav-houses"><span class='icon-field'><i class="fa fa-home "></i></span> Houses</a>
				<a href="index.php?page=tenants" class="nav-item nav-tenants"><span class='icon-field'><i class="fa fa-user-friends "></i></span> Tenants</a>
				<a href="index.php?page=invoices" class="nav-item nav-invoices"><span class='icon-field'><i class="fa fa-file-invoice "></i></span> Payments</a>
				<a href="index.php?page=reports" class="nav-item nav-reports"><span class='icon-field'><i class="fa fa-list-alt "></i></span> Reports</a>
				<?php if($_SESSION['login_type'] == 1): ?>
				<a href="index.php?page=users" class="nav-item nav-users"><span class='icon-field'><i class="fa fa-users "></i></span> Users</a>
				<!-- <a href="index.php?page=site_settings" class="nav-item nav-site_settings"><span class='icon-field'><i class="fa fa-cogs text-danger"></i></span> System Settings</a> -->
			<?php endif; ?>
		</div>

</nav>
<script>
	$('.nav_collapse').click(function(){
		console.log($(this).attr('href'))
		$($(this).attr('href')).collapse()
	})
	$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')
</script>

<?php
session_start();

// Timeout settings
$timeout_duration = 600; // 10 minutes in seconds

// If user is logged in
if (isset($_SESSION['login_id'])) {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        // Save current page before logout
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        // Destroy session
        session_unset();
        session_destroy();

        // Redirect to landing
        header("Location: landing.php");
        exit();
    }

    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();
} else {
    // If not on login or landing, send to landing
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
    position: fixed;       /* stick to left */
    top: 0;
    left: 0;
    bottom: 0;
    width: 220px;
    background-color: #B0E0E6; ;
    color: white;
    overflow-y: auto;      /* scroll if too tall */
    padding-top: 1rem;
}

/* Sidebar links */
nav#sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    transition: background 0.2s;
}

/* Hover and active state */
nav#sidebar a:hover,
nav#sidebar a.active {
    background-color: #495057;
}

/* Icon spacing */
nav#sidebar .icon-field {
    width: 20px;
    display: inline-block;
}
.sidebar-links {
    flex-grow: 1;
}
.logout-link {
    position: absolute;
    bottom: 20px;
    left: 20px;
    color: #ff4d4d;
    font-weight: bold;
    text-decoration: none;
}

.logout-link:hover {
    color: #007bff; /* powder blue hover */
    text-decoration: underline;
}
/* Push main content right */
body {
    margin-left: 220px;  /* equal to sidebar width */
}
</style>

<nav id="sidebar">
    <a href="index.php?page=home" class="nav-home"><span class="icon-field"><i class="fa fa-tachometer-alt"></i></span> Dashboard</a>
    <a href="index.php?page=categories" class="nav-categories"><span class="icon-field"><i class="fa fa-th-list"></i></span> House Type</a>
    <a href="index.php?page=houses" class="nav-houses"><span class="icon-field"><i class="fa fa-home"></i></span> Houses</a>
    <a href="index.php?page=tenants" class="nav-tenants"><span class="icon-field"><i class="fa fa-user-friends"></i></span> Tenants</a>
    <a href="index.php?page=invoices" class="nav-invoices"><span class="icon-field"><i class="fa fa-file-invoice"></i></span> Payments</a>
    <a href="index.php?page=reports" class="nav-reports"><span class="icon-field"><i class="fa fa-list-alt"></i></span> Reports</a>
    

    <?php if($_SESSION['login_type'] == 1): ?>
        <a href="index.php?page=users" class="nav-users"><span class="icon-field"><i class="fa fa-users"></i></span> Users</a>
    <?php endif; ?>
    <a href="logout.php" class="logout-link text-danger">
    <i class="fas fa-sign-out-alt"></i> Logout
</a>
</nav>
<!-- Top Navbar -->
<nav class="navbar navbar-expand navbar-light bg-light fixed-top">
    <a class="navbar-brand" href="#">Jovic Park Rental Management System</a>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Administrator
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="adminDropdown">
                <a class="dropdown-item" href="index.php?page=manage_settings"><i class="fas fa-cogs"></i> Manage Settings</a>
                <a class="dropdown-item" href="index.php?page=change_password"><i class="fas fa-key"></i> Change Password</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </li>
    </ul>
</nav>

<script>
// Highlight the active page
let currentPage = '<?php echo isset($_GET["page"]) ? $_GET["page"] : "home"; ?>';
$('.nav-' + currentPage).addClass('active');
</script>


<script>
$('.nav_collapse').click(function(){
    console.log($(this).attr('href'))
    $($(this).attr('href')).collapse()
})
$('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active')
</script>
