<?php
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

class Action {
    private $db;

    public function __construct() {
        include 'db_connect.php';
        $this->db = $conn;
    }

    public function __destruct() {
        $this->db->close();
    }

    // ===== LOGIN =====
public function login($type = null) {
    // Ensure no extra PHP notices mess with JSON output
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

    // Start session only if none exists
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        echo json_encode(['status'=>'error','msg'=>'Username and password required']);
        exit;
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // First search in users table
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $qry = $stmt->get_result();

    if ($qry->num_rows === 0) {
        // Fallback to tenants table
        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $qry = $stmt->get_result();

        if ($qry->num_rows === 0) {
            echo json_encode(['status'=>'error','msg'=>'User not found']);
            exit;
        }
        $table = 'tenants';
    } else {
        $table = 'users';
    }

    $user = $qry->fetch_assoc();
    $passwordFromDb = $user['password'];
    $passwordInfo = password_get_info($passwordFromDb);
    $passwordOk = false;

    if ($passwordInfo['algo'] !== 0) {
        $passwordOk = password_verify($password, $passwordFromDb);
    } elseif ($passwordFromDb === md5($password) || $passwordFromDb === $password) {
        $passwordOk = true;
    }

    if (!$passwordOk) {
        echo json_encode(['status'=>'error','msg'=>'Password is incorrect']);
        exit;
    }

    // Auto-upgrade password to bcrypt if still plain or md5
    if ($passwordInfo['algo'] === 0) {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $updateStmt = $this->db->prepare("UPDATE $table SET password = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newHash, $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }

    // Store session
    $_SESSION['login_id'] = $user['id'];
    $_SESSION['user_type'] = ($table === 'tenants') ? 'tenant' : 'user';
    foreach ($user as $key => $value) {
        if (!is_numeric($key)) $_SESSION['login_'.$key] = $value;
    }

    echo json_encode(['status'=>'success','msg'=>'Login successful']);
    exit;
}

    // ===== SIGNUP =====
   public function signup() {
    if (!isset($_POST['fullname'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        return json_encode(['status' => 'error', 'msg' => 'All fields are required']);
    }

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Block anyone from setting themselves as admin
    if (isset($_POST['type']) && $_POST['type'] === 'admin') {
        return json_encode(['status' => 'error', 'msg' => 'Admin accounts can only be created by the system administrator']);
    }

    if ($password !== $confirm_password) {
        return json_encode(['status' => 'error', 'msg' => 'Passwords do not match']);
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return json_encode(['status' => 'error', 'msg' => 'Username can only contain letters, numbers, and underscores']);
    }

    // Check if username already exists
    $checkStmt = $this->db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        return json_encode(['status' => 'error', 'msg' => 'Username already taken']);
    }
    $checkStmt->close();

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Save user as normal (staff or tenant depending on your system default)
    $type = 'staff'; // or 'tenant' depending on your needs
    $insertStmt = $this->db->prepare("INSERT INTO users (name, username, email, password, type) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssss", $fullname, $username, $email, $hashedPassword, $type);

    if ($insertStmt->execute()) {
        return json_encode(['status' => 'success', 'redirect' => 'login.php']);
    } else {
        return json_encode(['status' => 'error', 'msg' => 'Failed to create account']);
    }
}

    // ===== LOGOUT =====
    public function logout($redirect = 'login.php') {
        session_unset();
        session_destroy();
        header("Location: $redirect");
        exit;
    }

    public function logout2() {
        $this->logout('../index.php');
    }

    // ===== USER MANAGEMENT =====
    public function save_user() {
    extract($_POST);
    $data = " name = '$name', username = '$username'";
    if (!empty($password)) $data .= ", password = '" . password_hash($password, PASSWORD_BCRYPT) . "'";
    $data .= ", type = '$type'";
    $establishment_id = $type == 1 ? 0 : ($establishment_id ?? 0);
    $data .= ", establishment_id = '$establishment_id'";

    // Check for duplicate username
    $chk = $this->db->query("SELECT * FROM users WHERE username='$username' AND id != '$id'")->num_rows;
    if ($chk > 0) return 2;

    // Only allow one admin
    if ($type == 'admin') { // Or type == 1 depending on your DB
        $check = $this->db->query("SELECT id FROM users WHERE type = 'admin' LIMIT 1");
        if ($check->num_rows > 0) {
            $existing = $check->fetch_assoc();
            if (empty($id) || $id != $existing['id']) {
                return json_encode(['status' => 'error', 'msg' => 'Only one admin account is allowed.']);
            }
        }
    }

    if (empty($id)) {
        $save = $this->db->query("INSERT INTO users SET $data");
    } else {
        $save = $this->db->query("UPDATE users SET $data WHERE id = $id");
    }

    return $save ? 1 : 0;
}

    public function delete_user() {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM users WHERE id = ".$id);
        return $delete ? 1 : 0;
    }

    // ===== UPDATE ACCOUNT =====
    public function update_account() {
        extract($_POST);
        $data = "name='".$firstname.' '.$lastname."', username='$email'";
        if(!empty($password)) $data .= ", password='".password_hash($password, PASSWORD_BCRYPT)."'";

        $chk = $this->db->query("SELECT * FROM users WHERE username='$email' AND id != '{$_SESSION['login_id']}'")->num_rows;
        if($chk > 0) return 2;

        $save = $this->db->query("UPDATE users SET $data WHERE id='{$_SESSION['login_id']}'");
        if($save){
            foreach ($_SESSION as $key => $value) unset($_SESSION[$key]);
            $_POST['username'] = $email;
            $_POST['password'] = $password;
            $this->login2();
            return 1;
        }
    }

    // ===== SETTINGS =====
    public function save_settings() {
        extract($_POST);
        $data = "name='".str_replace("'","&#x2019;",$name)."', email='$email', contact='$contact', about_content='".htmlentities(str_replace("'","&#x2019;",$about))."'";
        if($_FILES['img']['tmp_name'] != ''){
            $fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
            move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
            $data .= ", cover_img='$fname'";
        }
        $chk = $this->db->query("SELECT * FROM system_settings");
        if($chk->num_rows > 0) $save = $this->db->query("UPDATE system_settings SET $data");
        else $save = $this->db->query("INSERT INTO system_settings SET $data");

        if($save){
            $query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_array();
            foreach ($query as $key => $value) if(!is_numeric($key)) $_SESSION['system'][$key] = $value;
            return 1;
        }
    }

    // ===== CATEGORIES =====
    public function save_category() {
        extract($_POST);
        $data = "name='$name'";
        if(empty($id)) $save = $this->db->query("INSERT INTO categories SET $data");
        else $save = $this->db->query("UPDATE categories SET $data WHERE id=$id");
        return $save ? 1 : 0;
    }

    public function delete_category() {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM categories WHERE id=$id");
        return $delete ? 1 : 0;
    }

    // ===== HOUSES =====
    public function save_house() {
        extract($_POST);
        $data = "house_no='$house_no', description='$description', category_id='$category_id', price='$price'";
        $chk = $this->db->query("SELECT * FROM houses WHERE house_no='$house_no'")->num_rows;
        if($chk > 0) return 2;

        if(empty($id)) $save = $this->db->query("INSERT INTO houses SET $data");
        else $save = $this->db->query("UPDATE houses SET $data WHERE id=$id");

        return $save ? 1 : 0;
    }

    public function delete_house() {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM houses WHERE id=$id");
        return $delete ? 1 : 0;
    }

    // ===== TENANTS =====
    public function save_tenant() {
        extract($_POST);
        $data = "firstname='$firstname', lastname='$lastname', middlename='$middlename', email='$email', contact='$contact', house_id='$house_id', date_in='$date_in'";
        if(empty($id)) $save = $this->db->query("INSERT INTO tenants SET $data");
        else $save = $this->db->query("UPDATE tenants SET $data WHERE id=$id");
        return $save ? 1 : 0;
    }

    public function delete_tenant() {
        extract($_POST);
        $delete = $this->db->query("UPDATE tenants SET status=0 WHERE id=$id");
        return $delete ? 1 : 0;
    }

    public function get_tdetails() {
        extract($_POST);
        $data = [];
        $tenants = $this->db->query("SELECT t.*, CONCAT(t.lastname, ', ', t.firstname, ' ', t.middlename) as name, h.house_no, h.price FROM tenants t INNER JOIN houses h ON h.id=t.house_id WHERE t.id={$id}");
        foreach($tenants->fetch_array() as $k => $v) if(!is_numeric($k)) $$k = $v;

        $months = floor(abs(strtotime(date('Y-m-d 23:59:59')) - strtotime($date_in.' 23:59:59')) / (30*60*60*24));
        $payable = $price * $months;
        $paidRes = $this->db->query("SELECT SUM(amount) as paid FROM payments WHERE tenant_id=$id");
        $paid = $paidRes->num_rows > 0 ? $paidRes->fetch_array()['paid'] : 0;
        $last_paymentRes = $this->db->query("SELECT * FROM payments WHERE tenant_id=$id ORDER BY unix_timestamp(date_created) DESC LIMIT 1");
        $last_payment = $last_paymentRes->num_rows > 0 ? date("M d, Y",strtotime($last_paymentRes->fetch_array()['date_created'])) : 'N/A';

        $data['months'] = $months;
        $data['payable'] = number_format($payable,2);
        $data['paid'] = number_format($paid,2);
        $data['last_payment'] = $last_payment;
        $data['outstanding'] = number_format($payable-$paid,2);
        $data['price'] = number_format($price,2);
        $data['name'] = ucwords($name);
        $data['rent_started'] = date('M d, Y',strtotime($date_in));

        return json_encode($data);
    }

    // ===== PAYMENTS =====
    public function save_payment() {
        extract($_POST);
        $data = '';
        foreach($_POST as $k => $v){
            if(!in_array($k,['id','ref_code']) && !is_numeric($k)) $data .= empty($data) ? " $k='$v'" : ", $k='$v'";
        }
        if(empty($id)){
            $save = $this->db->query("INSERT INTO payments SET $data");
            $id=$this->db->insert_id;
        }else $save = $this->db->query("UPDATE payments SET $data WHERE id=$id");
        return $save ? 1 : 0;
    }

    public function delete_payment() {
        extract($_POST);
        $delete = $this->db->query("DELETE FROM payments WHERE id=$id");
        return $delete ? 1 : 0;
    }
}
?>
