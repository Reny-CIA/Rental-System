<?php session_start(); ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>OTP Verification - Jovic Park</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #eef3f8;
      font-family: "Poppins", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
    }
    .otp-card {
      max-width: 440px;
      margin: 9vh auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 16px 50px rgba(0,0,0,.10);
      overflow: hidden;
      border: 1px solid rgba(0,0,0,.04);
    }
    .otp-header {
      background: linear-gradient(135deg, #0aa4e6, #007baf);
      padding: 22px 20px;
      color: #fff;
      text-align: center;
    }
    .otp-header h3 {
      margin: 0;
      font-weight: 700;
      letter-spacing: .3px;
    }
    .otp-body {
      padding: 22px 20px 10px 20px;
    }
    .otp-input {
      letter-spacing: .15rem;
    }
    .otp-badge {
      background:#f7fbff;
      border: 1px dashed #b6dfff;
      color:#0f6fa4;
      padding:10px 12px;
      border-radius:10px;
      font-size:.92rem;
    }
    .btn-brand {
      background:#007baf;
      border:none;
      font-weight:600;
    }
    .btn-brand:hover {
      background:#0a8acd;
    }
    .tiny {
      color:#789;
      font-size:.85rem;
    }
  </style>
</head>
<body>

<div class="otp-card">
  <div class="otp-header">
    <h3>🔐 OTP Verification</h3>
    <div class="tiny">We’ll email you a 6-digit code (valid 10 minutes)</div>
  </div>
  <div class="otp-body">
    <form id="request-otp">
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <input type="hidden" name="purpose" value="reset">
      <button class="btn btn-brand w-100" type="submit">Send OTP</button>
      <div id="request-msg" class="mt-2 tiny"></div>
    </form>

    <hr class="my-3">

    <form id="verify-otp">
      <div class="mb-3">
        <label class="form-label">Enter 6-digit OTP</label>
        <input type="text" name="otp" maxlength="6" class="form-control otp-input" placeholder="••••••" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email address (same as above)</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <div class="otp-badge mb-3">
        Tip: Check your spam folder if you don’t see the email within 1–2 minutes.
      </div>
      <button class="btn btn-success w-100" type="submit">Verify OTP</button>
      <div id="verify-msg" class="mt-2 tiny"></div>
    </form>
  </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script>
$('#request-otp').on('submit', function(e){
  e.preventDefault();
  const btn = $(this).find('button[type=submit]');
  btn.prop('disabled', true).text('Sending...');
  $('#request-msg').removeClass('text-danger text-success').text('');
  $.post('generate_otp.php', $(this).serialize(), function(resp){
    if(resp.status === 'success'){
      $('#request-msg').addClass('text-success').text(resp.msg);
    } else {
      $('#request-msg').addClass('text-danger').text(resp.msg || 'Failed to send OTP');
    }
  }, 'json').fail(function(){
    $('#request-msg').addClass('text-danger').text('Network error');
  }).always(function(){
    btn.prop('disabled', false).text('Send OTP');
  });
});

$('#verify-otp').on('submit', function(e){
  e.preventDefault();
  const btn = $(this).find('button[type=submit]');
  btn.prop('disabled', true).text('Verifying...');
  $('#verify-msg').removeClass('text-danger text-success').text('');
  $.post('verify_otp.php', $(this).serialize(), function(resp){
    if(resp.status === 'success'){
      $('#verify-msg').addClass('text-success').text(resp.msg);
      // TODO: Redirect user or open reset form
      // location.href = 'reset_password.php?email=' + encodeURIComponent($('input[name=email]', '#verify-otp').val());
    } else {
      $('#verify-msg').addClass('text-danger').text(resp.msg || 'Invalid code');
    }
  }, 'json').fail(function(){
    $('#verify-msg').addClass('text-danger').text('Network error');
  }).always(function(){
    btn.prop('disabled', false).text('Verify OTP');
  });
});
</script>
</body>
</html>
