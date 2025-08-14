<?php include 'db_connect.php' ?>
<style>
   span.float-right.summary_icon {
    font-size: 3rem;
    position: absolute;
    right: 1rem;
    top: 0;
}
.imgs{
		margin: .5em;
		max-width: calc(100%);
		max-height: calc(100%);
	}
	.imgs img{
		max-width: calc(100%);
		max-height: calc(100%);
		cursor: pointer;
	}
	#imagesCarousel,#imagesCarousel .carousel-inner,#imagesCarousel .carousel-item{
		height: 60vh !important;background: black;
	}
	#imagesCarousel .carousel-item.active{
		display: flex !important;
	}
	#imagesCarousel .carousel-item-next{
		display: flex !important;
	}
	#imagesCarousel .carousel-item img{
		margin: auto;
	}
	#imagesCarousel img{
		width: auto!important;
		height: auto!important;
		max-height: calc(100%)!important;
		max-width: calc(100%)!important;
	}
</style>

<div class="containe-fluid">
	<div class="row mt-3 ml-3 mr-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <?php echo "Welcome back ". $_SESSION['login_name']."!"  ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body bg-primary">
                                    <div class="card-body text-white">
                                        <span class="float-right summary_icon"> <i class="fa fa-home "></i></span>
                                        <h4><b>
                                            <?php echo $conn->query("SELECT * FROM houses")->num_rows ?>
                                        </b></h4>
                                        <p><b>Total Houses</b></p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <a href="index.php?page=houses" class="text-primary float-right">View List <span class="fa fa-angle-right"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body bg-warning">
                                    <div class="card-body text-white">
                                        <span class="float-right summary_icon"> <i class="fa fa-user-friends "></i></span>
                                        <h4><b>
                                            <?php echo $conn->query("SELECT * FROM tenants where status = 1 ")->num_rows ?>
                                        </b></h4>
                                        <p><b>Total Tenants</b></p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <a href="index.php?page=tenants" class="text-primary float-right">View List <span class="fa fa-angle-right"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body bg-success">
                                    <div class="card-body text-white">
                                        <span class="float-right summary_icon"> <i class="fa fa-file-invoice "></i></span>
                                        <h4><b>
                                            <?php 
                                             $payment = $conn->query("SELECT sum(amount) as paid FROM payments where date(date_created) = '".date('Y-m-d')."' "); 
                                             echo $payment->num_rows > 0 ? number_format($payment->fetch_array()['paid'],2) : 0;
                                             ?>
                                        </b></h4>
                                        <p><b>Payments This Month</b></p>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <a href="index.php?page=invoices" class="text-primary float-right">View Payments <span class="fa fa-angle-right"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>      			
        </div>
    </div>
</div>
<script>
	$('#manage-records').submit(function(e){
        e.preventDefault()
        start_load()
        $.ajax({
            url:'ajax.php?action=save_track',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                resp=JSON.parse(resp)
                if(resp.status==1){
                    alert_toast("Data successfully saved",'success')
                    setTimeout(function(){
                        location.reload()
                    },800)

                }
                
            }
        })
    })
    $('#tracking_id').on('keypress',function(e){
        if(e.which == 13){
            get_person()
        }
    })
    $('#check').on('click',function(e){
            get_person()
    })
    function get_person(){
            start_load()
        $.ajax({
                url:'ajax.php?action=get_pdetails',
                method:"POST",
                data:{tracking_id : $('#tracking_id').val()},
                success:function(resp){
                    if(resp){
                        resp = JSON.parse(resp)
                        if(resp.status == 1){
                            $('#name').html(resp.name)
                            $('#address').html(resp.address)
                            $('[name="person_id"]').val(resp.id)
                            $('#details').show()
                            end_load()

                        }else if(resp.status == 2){
                            alert_toast("Unknow tracking id.",'danger');
                            end_load();
                        }
                    }
                }
            })
    }
</script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connect.php'; 

if(!isset($_SESSION['login_id'])) header('location:login.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jovic Park Rental Management System - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<style>
/* Sidebar */
#sidebar {
    width: 220px;
    position: fixed;
    top: 56px; /* height of navbar */
    left: 0;
    bottom: 0;
    background: powderblue;
    color: white;
    padding-top: 20px;
    overflow-y: auto;
}

#sidebar a {
    color: white;
    display: block;
    padding: 10px 20px;
    text-decoration: none;
    transition: background 0.2s;
}

#sidebar a:hover, #sidebar a.active {
    background: #87aade;
}

/* Main content */
#view-panel {
    margin-left: 220px;
    padding: 20px;
}

/* Dashboard cards */
span.float-right.summary_icon {
    font-size: 3rem;
    position: absolute;
    right: 1rem;
    top: 0;
}
.card-body.bg-primary, .card-body.bg-warning, .card-body.bg-success, 
.card-body.bg-danger, .card-body.bg-secondary {
    min-height: 150px;
    position: relative;
}
.navbar-dark.bg-dark {
    background-color: #b0c4de !important;
}
.navbar-dark .navbar-brand, .navbar-dark .nav-link {
    color: white;
}
</style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">Jovic Park</a>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                Administrator
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="index.php?page=settings"><i class="fas fa-cogs"></i> Manage Settings</a>
                <a class="dropdown-item" href="index.php?page=change_password"><i class="fas fa-key"></i> Change Password</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </li>
    </ul>
</nav>

<!-- Sidebar -->
<div id="sidebar">
    <a href="index.php?page=home" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="index.php?page=categories"><i class="fas fa-layer-group"></i> House Type</a>
    <a href="index.php?page=houses"><i class="fas fa-home"></i> Houses</a>
    <a href="index.php?page=tenants"><i class="fas fa-user-friends"></i> Tenants</a>
    <a href="index.php?page=invoices"><i class="fas fa-file-invoice"></i> Payments</a>
    <a href="index.php?page=reports"><i class="fas fa-chart-line"></i> Reports</a>
    <?php if($_SESSION['login_type'] == 1): ?>
        <a href="index.php?page=users"><i class="fas fa-users-cog"></i> Users</a>
    <?php endif; ?>
</div>

<!-- Main Content -->
<div id="view-panel">
    <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
    <hr>
    <div class="card mb-4">
        <div class="card-body">
            <h4>Welcome back, <?php echo $_SESSION['login_name']; ?>!</h4>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row">

        <!-- Total Houses -->
        <div class="col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-body bg-primary text-white position-relative">
                    <span class="float-right summary_icon"><i class="fas fa-home"></i></span>
                    <h4><b><?php echo $conn->query("SELECT * FROM houses")->num_rows; ?></b></h4>
                    <p><b>Total Houses</b></p>
                </div>
                <div class="card-footer">
                    <a href="index.php?page=houses" class="text-primary float-right">View List <i class="fas fa-angle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Total Tenants -->
        <div class="col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-body bg-warning text-white position-relative">
                    <span class="float-right summary_icon"><i class="fas fa-user-friends"></i></span>
                    <h4><b><?php echo $conn->query("SELECT * FROM tenants WHERE status = 1")->num_rows; ?></b></h4>
                    <p><b>Total Tenants</b></p>
                </div>
                <div class="card-footer">
                    <a href="index.php?page=tenants" class="text-warning float-right">View List <i class="fas fa-angle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Payments This Month -->
        <div class="col-md-4 mb-3">
            <div class="card border-success">
                <div class="card-body bg-success text-white position-relative">
                    <span class="float-right summary_icon"><i class="fas fa-file-invoice"></i></span>
                    <?php
                        $payment = $conn->query("SELECT SUM(amount) as paid FROM payments WHERE MONTH(date_created)=MONTH(NOW()) AND YEAR(date_created)=YEAR(NOW())")->fetch_assoc()['paid'];
                    ?>
                    <h4><b><?php echo number_format($payment ?? 0,2); ?></b></h4>
                    <p><b>Payments This Month</b></p>
                </div>
                <div class="card-footer">
                    <a href="index.php?page=invoices" class="text-success float-right">View Payments <i class="fas fa-angle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Vacant Houses -->
        <div class="col-md-6 mb-3">
            <div class="card border-secondary">
                <div class="card-body bg-secondary text-white position-relative">
                    <span class="float-right summary_icon"><i class="fas fa-door-open"></i></span>
                    <?php
                    $vacantTotal = $conn->query("SELECT COUNT(*) as cnt FROM houses h WHERE NOT EXISTS (SELECT 1 FROM tenants t WHERE t.house_id=h.id AND t.status=1)")->fetch_assoc()['cnt'];
                    ?>
                    <h4><b><span id="vacant-count"><?php echo $vacantTotal; ?></span></b></h4>
                    <p><b>Vacant Houses</b></p>

                    <a href="#" class="text-light" data-toggle="collapse" data-target="#vacantList">View Details <i class="fas fa-angle-down"></i></a>
                    <div id="vacantList" class="collapse mt-3">
                        <?php
                        $vacant = $conn->query("SELECT h.house_number, c.name as type, h.price FROM houses h JOIN categories c ON h.category_id=c.id WHERE NOT EXISTS (SELECT 1 FROM tenants t WHERE t.house_id=h.id AND t.status=1)");
                        if($vacant->num_rows>0):
                        ?>
                        <table class="table table-sm table-dark">
                            <thead>
                                <tr>
                                    <th>House Number</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$vacant->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['house_number']; ?></td>
                                    <td><?php echo $row['type']; ?></td>
                                    <td><?php echo number_format($row['price'],2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p>No vacant houses available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="index.php?page=houses" class="text-light float-right">View All Houses <i class="fas fa-angle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Tenant Arrears -->
        <div class="col-md-6 mb-3">
            <div class="card border-danger">
                <div class="card-body bg-danger text-white position-relative">
                    <span class="float-right summary_icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <?php
                        $res = $conn->query("SELECT COUNT(*) as cnt FROM payments p WHERE p.amount_due>p.amount_paid");
                        $arrearsTotal = $res ? $res->fetch_assoc()['cnt'] : 0;
                    ?>
                    <h4><b><?php echo $arrearsTotal; ?></b></h4>
                    <p><b>Tenant Arrears</b></p>

                    <a href="#" class="text-light" data-toggle="collapse" data-target="#arrearsList">View Details <i class="fas fa-angle-down"></i></a>
                    <div id="arrearsList" class="collapse mt-3">
                        <form method="GET" action="arrears_pdf.php" class="mb-3 form-inline">
                            <label class="mr-2 text-white">Start Date:</label>
                            <input type="date" name="start_date" class="form-control mr-3" required>
                            <label class="mr-2 text-white">End Date:</label>
                            <input type="date" name="end_date" class="form-control mr-3" required>
                            <button type="submit" class="btn btn-light btn-sm">Download PDF</button>
                        </form>

                        <?php
                        $arrears = $conn->query("SELECT t.name as tenant_name, h.house_number, p.amount_due - p.amount_paid as rent_due, DATE_FORMAT(p.date_created,'%Y-%m') as period FROM payments p JOIN tenants t ON p.tenant_id=t.id JOIN houses h ON t.house_id=h.id WHERE p.amount_due>p.amount_paid");
                        if($arrears->num_rows>0):
                        ?>
                        <table class="table table-sm table-dark">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>House</th>
                                    <th>Rent Due</th>
                                    <th>Period</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row=$arrears->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['tenant_name']; ?></td>
                                    <td><?php echo $row['house_number']; ?></td>
                                    <td><?php echo number_format($row['rent_due'],2); ?></td>
                                    <td><?php echo $row['period']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p>No arrears at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="index.php?page=arrears_report" class="text-light float-right">View All Arrears <i class="fas fa-angle-right"></i></a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Auto-refresh Vacant and Arrears Counts
    function refreshDashboard() {
        $.get('ajax.php?action=get_vacant_count', data => $('#vacant-count').text(data));
        $.get('ajax.php?action=get_arrears_count', data => $('#arrears-count').text(data));
    }
    setInterval(refreshDashboard, 10000);
</script>

</body>
</html>
