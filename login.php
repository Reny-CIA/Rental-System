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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>
/* --- your CSS unchanged --- */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}
body {
  height: 100vh;
  background: url('assets/uploads/house-bg.jpg') no-repeat center center/cover;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  overflow: hidden;
}
.page-title {
  font-size: 3rem;
  font-weight: 700;
  color: black;
  text-shadow: 0 0 15px rgba(0, 200, 255, 0.6);
  margin-bottom: 20px;
  z-index: 10;
  text-align: center;
  padding: 0 10px;
}
#login-wrapper {
  position: relative;
  z-index: 5;
}
#login-right {
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 40px;
  width: 380px;
  max-width: 95%;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}
.login-card-title {
  font-size: 2rem;
  font-weight: 600;
  text-align: center;
  color: #007baf;
  margin-bottom: 20px;
}
.form-control {
  background: rgba(255, 255, 255, 0.8);
  border: none;
  padding: 12px 15px;
  font-size: 1.1em;
  color: #007baf;
  border-radius: 5px;
  margin-bottom: 20px;
}
.form-control::placeholder {
  color: #007baf;
}
.form-control:focus {
  outline: none;
  border: 1px solid #00aaff;
  box-shadow: 0 0 8px #00aaff;
}
.btn-primary {
  background: #00aaff;
  border: none;
  padding: 12px;
  font-size: 1.2em;
  font-weight: 500;
  width: 100%;
  color: white;
  transition: all 0.3s ease;
  border-radius: 5px;
}
.btn-primary:hover {
  background: #008fcc;
  transform: scale(1.05);
  box-shadow: 0 0 15px #00aaff;
}
.alert {
  background: rgba(255, 0, 0, 0.7);
  color: white;
  border: none;
  padding: 10px;
  border-radius: 5px;
}
@media (max-width: 420px) {
  .page-title {
    font-size: 2rem;
  }
}
</style>
</head>
<body>

<div class="page-title">Welcome to Jovic Park Rental Management System</div>

<main>
  <div id="login-wrapper" class="container d-flex justify-content-center">
    <div id="login-right">
      <div class="login-card-title">Login</div>
      <form id="login-form" autocomplete="off">
        <div class="mb-3">
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter Username" required>
        </div>
        <div class="mb-4">
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter Password" required>
        </div>
        <center>
          <button type="submit" class="btn btn-primary">Login</button>
        </center>
        <div class="text-center mt-3">
          <a href="register.php" style="color:#007baf; font-weight:500; text-decoration:none;">Don't have an account? Sign up</a>
        </div>
        <div class="text-center mt-2">
          <a href="forgot_password.php" style="color:#007baf; font-weight:500; text-decoration:none;">Forgot password?</a>
        </div>
      </form>
    </div>
  </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        dataType: 'json', // expecting JSON from PHP
        error: function(err){
            console.error(err);
            $('#login-form').prepend('<div class="alert alert-danger">Server error. Please try again.</div>');
            btn.attr('disabled', false).html('Login');
        },
        success: function(resp){
            console.log("Login Response:", resp);

            if(resp.status === 'success'){
                if(resp.debug && resp.debug.table === 'tenants'){
                    location.href = 'index.php?page=tenant_dashboard';
                } else {
                    location.href = 'index.php?page=home';
                }
            } else {
                $('#login-form').prepend('<div class="alert alert-danger">'+ (resp.msg || 'Username or password is incorrect.') +'</div>');
                btn.attr('disabled', false).html('Login');
            }
        }
    });
});
</script>

</body>
</html>
