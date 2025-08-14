<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Welcome to Jovic Park Rental Management System</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<style>
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
  color: #007baf;
  text-shadow: 0 0 15px rgba(0, 200, 255, 0.6);
  margin-bottom: 20px;
  z-index: 10;
}

#register-wrapper {
  position: relative;
  z-index: 5;
}

#register-right {
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 40px;
  width: 380px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.register-card-title {
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

/* 👁 Password toggle styles */
.password-wrapper {
  position: relative;
}
.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #007baf;
  font-size: 1.1em;
}

@media (max-width: 420px) {
  #register-right {
    width: 90%;
    padding: 20px;
  }
  .page-title {
    font-size: 2rem;
  }
}
</style>
</head>
<body>

<div class="page-title">Welcome to Jovic Park Rental Management System</div>

<main>
  <div id="register-wrapper">
    <div id="register-right">
      <div class="register-card-title">Register</div>
      <form id="register-form" autocomplete="off">
        <div class="form-group mb-3">
          <input type="text" name="name" class="form-control" placeholder="Full Name" required>
        </div>
        <div class="form-group mb-3">
          <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
        </div>
        <div class="form-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group mb-3 password-wrapper">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <span class="password-toggle" data-target="password">&#128065;</span>
        </div>
        <div class="form-group mb-3 password-wrapper">
          <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
          <span class="password-toggle" data-target="confirm_password">&#128065;</span>
        </div>
        <div id="error-msg" class="text-danger mb-3" style="display:none;"></div>
        <div id="success-msg" class="text-success mb-3" style="display:none;"></div>
        <button type="submit" class="btn btn-primary">Register</button>
        <div style="text-align:center; margin-top:15px;">
          <a href="forgot_password.php" style="color:#007baf; font-weight:500; text-decoration:none;">Forgot password</a>
        </div>
        <div style="text-align:center; margin-top:8px;">
          <a href="login.php" style="color:#007baf; font-weight:500; text-decoration:none;">Login</a>
        </div>
      </form>
    </div>
  </div>
</main>

<script src="assets/js/jquery.min.js"></script>
<script>
// Toggle password visibility
$('.password-toggle').on('click', function(){
    let target = $(this).data('target');
    let input = $('input[name="'+target+'"]');
    let type = input.attr('type') === 'password' ? 'text' : 'password';
    input.attr('type', type);
});

// Form submit
$('#register-form').on('submit', function(e) {
    e.preventDefault();
    $('#error-msg').hide();
    $('#success-msg').hide();

    let pass = $('input[name="password"]').val();
    let confirm = $('input[name="confirm_password"]').val();
    if (pass !== confirm) {
        $('#error-msg').text('Passwords do not match!').show();
        return;
    }

    $.ajax({
        url: 'ajax.php?action=signup',
        method: 'POST',
        data: $(this).serialize(),
        success: function(resp) {
            if (resp == 1) {
                $('#success-msg').text('Account created successfully! Redirecting to login...').show();
                setTimeout(function(){
                    window.location.href = 'login.php';
                }, 2000);
            } else if (resp == 2) {
                $('#error-msg').text('Username already exists!').show();
            } else {
                $('#error-msg').text('Registration failed. Please try again.').show();
            }
        }
    });
});
</script>

</body>
</html>
