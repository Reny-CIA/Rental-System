<?php
session_start();
?>

<div class="container-fluid">
    <form id="change_password_form">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" class="form-control" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>

<script>
$('#change_password_form').submit(function(e){
    e.preventDefault();
    $.ajax({
        url: 'ajax.php?action=change_password',
        method: 'POST',
        data: $(this).serialize(),
        success: function(resp){
            if(resp == 1){
                alert_toast("Password updated successfully!", 'success');
                $('.modal').modal('hide');
            } else {
                alert_toast("Error updating password", 'danger');
            }
        }
    });
});
</script>
