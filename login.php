<?php 
session_start();
include('./db_connect.php');
ob_start();
if (!isset($_SESSION['system'])) {
    $system = $conn->query("SELECT * FROM system_settings LIMIT 1")->fetch_array();
    foreach ($system as $k => $v) {
        $_SESSION['system'][$k] = $v;
    }
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title><?php echo $_SESSION['system']['name'] ?></title>
<?php include('./header.php'); ?>
<style>
body {
  margin: 0;
  height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: url('assets/uploads/login-bg.jpg') no-repeat center center/cover;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  overflow: hidden;
  color: white;
}

/* Overlay for dim effect */
body::before {
  content: "";
  position: fixed;
  top: 0; left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  z-index: 0;
}

/* Page Title */
.page-title {
  font-size: 3.5rem;
  font-weight: 800;
  text-shadow: 0 0 20px rgba(0, 255, 255, 0.8);
  margin-bottom: 2rem;
  z-index: 10;
  animation: glow 2s infinite alternate;
}

/* Glow animation for title */
@keyframes glow {
  from { text-shadow: 0 0 10px #00f7ff, 0 0 20px #00f7ff, 0 0 30px #00f7ff; }
  to { text-shadow: 0 0 20px #ffffff, 0 0 30px #00f7ff, 0 0 40px #00f7ff; }
}

/* Login card container */
#login-right {
  position: relative;
  z-index: 1;
  width: 380px;
  background: rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 30px;
  color: white;
  box-sizing: border-box;
}

/* Neon rotating border */
#login-right::before {
  content: '';
  position: absolute;
  top: -3px;
  left: -3px;
  right: -3px;
  bottom: -3px;
  border-radius: 20px;
  background: linear-gradient(270deg, #00f7ff, #ffffff, #00f7ff);
  background-size: 600% 600%;
  animation: border-rotate 5s linear infinite;
  z-index: -1;
}

@keyframes border-rotate {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* Card title */
.login-card-title {
  font-weight: 700;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  text-align: center;
}

/* Inputs */
.form-control {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  padding: 10px;
}

.form-control:focus {
  border: 1px solid #00f7ff;
  outline: none;
  box-shadow: 0 0 10px #00f7ff;
}

/* Login button */
.btn-primary {
  background: linear-gradient(135deg, #00c6ff, #0072ff);
  border: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0072ff, #00c6ff);
  transform: scale(1.05);
  box-shadow: 0 0 20px #00f7ff;
}

.alert {
  background: rgba(255, 0, 0, 0.7);
  color: white;
  border: none;
}
</style>
</head>
<body>

<div class="page-title">JOvic Park</div>

<main>
    <div id="login-right">
        <div class="w-100">
            <div class="card col-md-12 mx-auto" style="background:transparent; border:none;">
                <div class="card-body">
                    <div class="login-card-title">Login</div>
                    <form id="login-form" autocomplete="off">
                        <div class="form-group mb-3">
                            <input type="text" id="username" name="username" class="form-control" placeholder="Enter Username" required>
                        </div>
                        <div class="form-group mb-4">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter Password" required>
                        </div>
                        <center>
                            <button type="submit" class="btn btn-primary btn-block col-md-6">Login</button>
                        </center>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
$('#login-form').submit(function(e){
    e.preventDefault();

    let btn = $('#login-form button[type="submit"]');
    btn.attr('disabled', true).html('Logging in...');

    $('.alert-danger').remove();

    $.ajax({
        url: 'ajax.php?action=login',
        method: 'POST',
        data: $(this).serialize(),
        error: function(err){
            console.log(err);
            btn.attr('disabled', false).html('Login');
        },
        success: function(resp){
            if(resp == 1){
                location.href = 'index.php?page=home';
            } else if (resp == 2){
                location.href = 'index.php?page=tenant_dashboard';
            } else {
                $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
                btn.attr('disabled', false).html('Login');
            }
        }
    });
});
</script>

</body>
</html>
