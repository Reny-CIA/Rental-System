<?php
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login() {
    extract($_POST);

    // Fetch user by username
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $qry = $stmt->get_result();

    if ($qry->num_rows > 0) {
        $user = $qry->fetch_assoc();

        $passwordFromDb = $user['password'];
        $passwordOk = false;

        // Check if bcrypt hash (starts with $2y$ or $2a$)
        if (password_get_info($passwordFromDb)['algo'] !== 0) {
            $passwordOk = password_verify($password, $passwordFromDb);
        } else {
            // Fallback to MD5 check
            if ($passwordFromDb === md5($password)) {
                $passwordOk = true;
                // Upgrade to bcrypt
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newHash, $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }

        if ($passwordOk) {
            foreach ($user as $key => $value) {
                if (!is_numeric($key)) {
                    $_SESSION['login_' . $key] = $value;
                }
            }
            return 1; // Success
        }
    }
    return 3; // Invalid login
}

	function login2() {
    extract($_POST);

    // Fetch user by email
    $stmt = $this->db->prepare("SELECT * FROM tenants WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $qry = $stmt->get_result();

    if ($qry->num_rows > 0) {
        $user = $qry->fetch_assoc();

        $passwordFromDb = $user['password'];
        $passwordOk = false;

        // Check if bcrypt
        if (password_get_info($passwordFromDb)['algo'] !== 0) {
            $passwordOk = password_verify($password, $passwordFromDb);
        } else {
            // Fallback to MD5 check
            if ($passwordFromDb === md5($password)) {
                $passwordOk = true;
                // Upgrade to bcrypt
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $this->db->prepare("UPDATE tenants SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newHash, $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }

        if ($passwordOk) {
            foreach ($user as $key => $value) {
                if (!is_numeric($key)) {
                    $_SESSION['login_' . $key] = $value;
                }
            }
            return 1; // Success
        }
    }
    return 3; // Invalid login
}

	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user(){
    extract($_POST);

    if (!empty($password)) {
        // Always hash new passwords with bcrypt
        $password = password_hash($password, PASSWORD_BCRYPT);
    }

    // Build query dynamically based on whether it's a new user or update
    if (empty($id)) {
        $stmt = $this->db->prepare("INSERT INTO users (name, username, password, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $username, $password, $type);
    } else {
        if (!empty($password)) {
            $stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, password = ?, type = ? WHERE id = ?");
            $stmt->bind_param("sssii", $name, $username, $password, $type, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, type = ? WHERE id = ?");
            $stmt->bind_param("ssii", $name, $username, $type, $id);
        }
    }

    $save = $stmt->execute();
    $stmt->close();

    return $save ? 1 : 0;
}

	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function signup(){
    extract($_POST);

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_BCRYPT);
    }

    $stmt = $this->db->prepare("INSERT INTO tenants (name, email, contact, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $contact, $password);

    $save = $stmt->execute();
    $stmt->close();

    return $save ? 1 : 0;
}

	function update_account(){
    extract($_POST);

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE tenants SET name = ?, email = ?, contact = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $contact, $password, $_SESSION['login_id']);
    } else {
        $stmt = $this->db->prepare("UPDATE tenants SET name = ?, email = ?, contact = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $contact, $_SESSION['login_id']);
    }

    $update = $stmt->execute();
    $stmt->close();

    return $update ? 1 : 0;
}

function change_admin_password(){
    extract($_POST);

    $id = $_SESSION['login_id']; // logged-in admin ID

    // Get current password hash from DB
    $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        return ['status' => 0, 'msg' => 'User not found.'];
    }

    $currentHash = $result['password'];

    // Verify current password (supports both bcrypt + MD5 from Step 1)
    $passwordOk = false;
    if (password_get_info($currentHash)['algo'] !== 0) {
        $passwordOk = password_verify($current_password, $currentHash);
    } else {
        if ($currentHash === md5($current_password)) {
            $passwordOk = true;
        }
    }

    if (!$passwordOk) {
        return ['status' => 0, 'msg' => 'Current password is incorrect.'];
    }

    // Check if new password and confirm match
    if ($new_password !== $confirm_password) {
        return ['status' => 0, 'msg' => 'New password and confirmation do not match.'];
    }

    // Hash new password (bcrypt only)
    $newHash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update DB
    $updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newHash, $id);
    $success = $updateStmt->execute();
    $updateStmt->close();

    return $success 
        ? ['status' => 1, 'msg' => 'Password updated successfully.'] 
        : ['status' => 0, 'msg' => 'Password update failed.'];
}

	function save_settings(){
		extract($_POST);
		$data = " name = '".str_replace("'","&#x2019;",$name)."' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
						$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
						$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
					$data .= ", cover_img = '$fname' ";

		}
		
		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
		$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
		foreach ($query as $key => $value) {
			if(!is_numeric($key))
				$_SESSION['system'][$key] = $value;
		}

			return 1;
				}
	}

	
	function save_category(){
		extract($_POST);
		$data = " name = '$name' ";
			if(empty($id)){
				$save = $this->db->query("INSERT INTO categories set $data");
			}else{
				$save = $this->db->query("UPDATE categories set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_category(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM categories where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_house(){
		extract($_POST);
		$data = " house_no = '$house_no' ";
		$data .= ", description = '$description' ";
		$data .= ", category_id = '$category_id' ";
		$data .= ", price = '$price' ";
		$chk = $this->db->query("SELECT * FROM houses where house_no = '$house_no' ")->num_rows;
		if($chk > 0 ){
			return 2;
			exit;
		}
			if(empty($id)){
				$save = $this->db->query("INSERT INTO houses set $data");
			}else{
				$save = $this->db->query("UPDATE houses set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_house(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM houses where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_tenant(){
		extract($_POST);
		$data = " firstname = '$firstname' ";
		$data .= ", lastname = '$lastname' ";
		$data .= ", middlename = '$middlename' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", house_id = '$house_id' ";
		$data .= ", date_in = '$date_in' ";
			if(empty($id)){
				
				$save = $this->db->query("INSERT INTO tenants set $data");
			}else{
				$save = $this->db->query("UPDATE tenants set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_tenant(){
		extract($_POST);
		$delete = $this->db->query("UPDATE tenants set status = 0 where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function get_tdetails(){
		extract($_POST);
		$data =array();
		$tenants =$this->db->query("SELECT t.*,concat(t.lastname,', ',t.firstname,' ',t.middlename) as name,h.house_no,h.price FROM tenants t inner join houses h on h.id = t.house_id where t.id = {$id} ");
		foreach($tenants->fetch_array() as $k => $v){
			if(!is_numeric($k)){
				$$k = $v;
			}
		}
		$months = abs(strtotime(date('Y-m-d')." 23:59:59") - strtotime($date_in." 23:59:59"));
		$months = floor(($months) / (30*60*60*24));
		$data['months'] = $months;
		$payable= abs($price * $months);
		$data['payable'] = number_format($payable,2);
		$paid = $this->db->query("SELECT SUM(amount) as paid FROM payments where id != '$pid' and tenant_id =".$id);
		$last_payment = $this->db->query("SELECT * FROM payments where id != '$pid' and tenant_id =".$id." order by unix_timestamp(date_created) desc limit 1");
		$paid = $paid->num_rows > 0 ? $paid->fetch_array()['paid'] : 0;
		$data['paid'] = number_format($paid,2);
		$data['last_payment'] = $last_payment->num_rows > 0 ? date("M d, Y",strtotime($last_payment->fetch_array()['date_created'])) : 'N/A';
		$data['outstanding'] = number_format($payable - $paid,2);
		$data['price'] = number_format($price,2);
		$data['name'] = ucwords($name);
		$data['rent_started'] = date('M d, Y',strtotime($date_in));

		return json_encode($data);
	}
	
	function save_payment() {
    extract($_POST);
    global $conn;

    // 1. Get tenant's rent from their assigned house
    $rent_qry = $conn->query("SELECT h.rent_amount 
        FROM tenants t 
        INNER JOIN houses h ON t.house_id = h.id 
        WHERE t.id = {$tenant_id}");
    $rent_row = $rent_qry->fetch_assoc();
    $rent_amount = isset($rent_row['rent_amount']) ? $rent_row['rent_amount'] : 0;

    // 2. Get last recorded balance (arrears)
    $bal_qry = $conn->query("SELECT balance FROM payments 
        WHERE tenant_id = {$tenant_id} 
        ORDER BY id DESC LIMIT 1");
    $prev_balance = ($bal_qry->num_rows > 0) ? $bal_qry->fetch_assoc()['balance'] : 0;

    // 3. Check if tenant has ever made a payment
    $first_payment = ($bal_qry->num_rows == 0);

    // 4. Deposits (only for first payment)
    $house_deposit = ($first_payment && isset($_POST['house_deposit'])) ? floatval($_POST['house_deposit']) : 0;
    $water_deposit = ($first_payment && isset($_POST['water_deposit'])) ? floatval($_POST['water_deposit']) : 0;
    $electricity_deposit = ($first_payment && isset($_POST['electricity_deposit'])) ? floatval($_POST['electricity_deposit']) : 0;

    // 5. Bills (charged every time)
    $water_bill = isset($_POST['water_bill']) ? floatval($_POST['water_bill']) : 0;

    // 6. Total due calculation
    $total_due = $rent_amount + $house_deposit + $water_deposit + $electricity_deposit + $water_bill + $prev_balance;

    // 7. Amount paid
    $amount_paid = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    // 8. New balance (avoid negatives)
    $new_balance = $total_due - $amount_paid;
    if ($new_balance < 0) {
        $new_balance = 0;
    }

    // 9. Save payment record
    $stmt = $conn->prepare("INSERT INTO payments (tenant_id, invoice, amount, total_due, balance, date_created) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdid", $tenant_id, $invoice, $amount_paid, $total_due, $new_balance);
    
    if ($stmt->execute()) {
        return 1; // success
    } else {
        return 0; // fail
    }
}


	function delete_payment(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM payments where id = ".$id);
		if($delete){
			return 1;
		}
	}
}