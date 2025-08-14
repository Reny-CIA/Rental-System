<?php 

?>

<div class="container-fluid">
	
	<div class="row">
	<div class="col-lg-12">
			<button class="btn btn-primary float-right btn-sm" id="new_user"><i class="fa fa-plus"></i> New user</button>
	</div>
	</div>
	<br>
	<div class="row">
		<div class="card col-lg-12">
			<div class="card-body">
				<table class="table-striped table-bordered col-md-12">
			<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">Name</th>
					<th class="text-center">Username</th>
					<th class="text-center">Type</th>
					<th class="text-center">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
 					include 'db_connect.php';
 					$type = array("","Admin","Staff","Alumnus/Alumna");
 					$users = $conn->query("SELECT * FROM users order by name asc");
 					$i = 1;
 					while($row= $users->fetch_assoc()):
				 ?>
				 <tr>
				 	<td class="text-center">
				 		<?php echo $i++ ?>
				 	</td>
				 	<td>
				 		<?php echo ucwords($row['name']) ?>
				 	</td>
				 	
				 	<td>
				 		<?php echo $row['username'] ?>
				 	</td>
				 	<td>
				 		<?php echo $type[$row['type']] ?>
				 	</td>
				 	<td>
				 		<center>
								<div class="btn-group">
								  <button type="button" class="btn btn-primary">Action</button>
								  <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    <span class="sr-only">Toggle Dropdown</span>
								  </button>
								  <div class="dropdown-menu">
								    <a class="dropdown-item edit_user" href="javascript:void(0)" data-id = '<?php echo $row['id'] ?>'>Edit</a>
								    <a class="dropdown-item change_password" href="javascript:void(0)" 
								       data-id="<?php echo $row['id'] ?>" 
								       data-name="<?php echo ucwords($row['name']) ?>">Change Password</a>
								    <div class="dropdown-divider"></div>
								    <a class="dropdown-item delete_user" href="javascript:void(0)" data-id = '<?php echo $row['id'] ?>'>Delete</a>
								  </div>
								</div>
								</center>
				 	</td>
				 </tr>
				<?php endwhile; ?>
			</tbody>
		</table>
			</div>
		</div>
	</div>

</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="changePasswordForm">
        <div class="modal-header">
          <h5 class="modal-title" id="changePasswordLabel">Change Password for <span id="userName"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span>&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <input type="hidden" id="userId" name="id">
          
          <div class="form-group">
            <label for="newPassword">New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="newPassword" name="password" required>
              <div class="input-group-append">
                <span class="input-group-text" onclick="togglePasswordVisibility('newPassword', 'changeToggleIcon')" style="cursor: pointer;">
                  <i id="changeToggleIcon" class="fa fa-eye"></i>
                </span>
              </div>
            </div>
            <small id="changeStrength" class="form-text"></small>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Password</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/password_strength.js"></script>
<script src="assets/js/toggle_password.js"></script>

<script>
	$('table').dataTable();
$('#new_user').click(function(){
	uni_modal('New User','manage_user.php')
})
$('.edit_user').click(function(){
	uni_modal('Edit User','manage_user.php?id='+$(this).attr('data-id'))
})
$('.delete_user').click(function(){
		_conf("Are you sure to delete this user?","delete_user",[$(this).attr('data-id')])
	})
	function delete_user($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_user',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}

// Change Password
$('.change_password').click(function(){
	let userId = $(this).data('id');
	let userName = $(this).data('name');
	$('#userId').val(userId);
	$('#userName').text(userName);
	$('#changePasswordModal').modal('show');
});

// Live password strength
$('#newPassword').on('input', function(){
	let strength = checkPasswordStrength(this.value);
	$('#changeStrength').text(strength.text).css('color', strength.color);
});

// Submit change password form
$('#changePasswordForm').submit(function(e){
	e.preventDefault();
	$.post('ajax.php?action=change_user_password', $(this).serialize(), function(response){
		if(response.status === 'success'){
			alert_toast('Password updated successfully', 'success');
			$('#changePasswordModal').modal('hide');
		} else {
			alert_toast('Error: ' + response.msg, 'danger');
		}
	}, 'json');
});
</script>
