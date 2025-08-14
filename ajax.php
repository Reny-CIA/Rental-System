<?php
ob_start();
$action = $_GET['action'];
include 'admin_class.php';
$crud = new Action();
if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'update_account'){
	$save = $crud->update_account();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}
if($action == "save_category"){
	$save = $crud->save_category();
	if($save)
		echo $save;
}

if($action == "delete_category"){
	$delete = $crud->delete_category();
	if($delete)
		echo $delete;
}
if($action == "save_house"){
	$save = $crud->save_house();
	if($save)
		echo $save;
}
if($action == "delete_house"){
	$save = $crud->delete_house();
	if($save)
		echo $save;
}

if($action == "save_tenant"){
	$save = $crud->save_tenant();
	if($save)
		echo $save;
}
if($action == "delete_tenant"){
	$save = $crud->delete_tenant();
	if($save)
		echo $save;
}
if($action == "get_tdetails"){
	$get = $crud->get_tdetails();
	if($get)
		echo $get;
}

if($action == "save_payment"){
	$save = $crud->save_payment();
	if($save)
		echo $save;
}
if($action == "delete_payment"){
	$save = $crud->delete_payment();
	if($save)
		echo $save;
}

ob_end_flush();
?>

<?php
session_start();

// Set timeout duration (seconds) — 10 minutes
$timeout_duration = 600;

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    // Store the current page so we can send them back here after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php"); // or your login page
    exit();
}

// Check last activity time
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session expired — store last page before logging out
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // Destroy session & redirect to login
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>

if ($action == 'logout2') {
    $crud->logout2();
    echo 1; // success flag
}

if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'update_account'){
	$save = $crud->update_account();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}
if($action == "save_category"){
	$save = $crud->save_category();
	if($save)
		echo $save;
}

if($action == "delete_category"){
	$delete = $crud->delete_category();
	if($delete)
		echo $delete;
}
if($action == "save_house"){
	$save = $crud->save_house();
	if($save)
		echo $save;
}
if(isset($_GET['action']) && $_GET['action'] == 'get_vacant_count'){
    include 'db_connect.php';

    $vacantResult = $conn->query("
        SELECT h.id
        FROM houses h
        LEFT JOIN tenants t ON t.house_id = h.id AND t.status = 1
        GROUP BY h.id
        HAVING COUNT(t.id) = 0
    ");

    echo $vacantResult->num_rows;
    exit;
}

if($action == "delete_house"){
	$save = $crud->delete_house();
	if($save)
		echo $save;
}

if($action == "save_tenant"){
	$save = $crud->save_tenant();
	if($save)
		echo $save;
}
if($action == "delete_tenant"){
	$save = $crud->delete_tenant();
	if($save)
		echo $save;
}
if($action == "get_tdetails"){
	$get = $crud->get_tdetails();
	if($get)
		echo $get;
}

if($action == "save_payment"){
	$save = $crud->save_payment();
	if($save)
		echo $save;
}
if($action == "delete_payment"){
	$save = $crud->delete_payment();
	if($save)
		echo $save;
}

if(isset($_GET['action']) && $_GET['action'] == 'get_arrears_count'){
    include 'db_connect.php';
    
    $arrearsResult = $conn->query("
        SELECT t.id
        FROM tenants t
        JOIN houses h ON t.house_id = h.id
        LEFT JOIN (
            SELECT tenant_id, SUM(amount) as paid
            FROM payments
            GROUP BY tenant_id
        ) p ON p.tenant_id = t.id
        WHERE t.status = 1 AND (h.rent_amount - IFNULL(p.paid,0)) > 0
    ");

    echo $arrearsResult->num_rows;
    exit;
}

if(isset($_GET['action']) && $_GET['action'] == 'change_password'){
    session_start();
    include 'db_connect.php';

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $response = ['status' => 'error', 'msg' => 'Invalid request'];

    if(empty($current_password) || empty($new_password) || empty($confirm_password)){
        $response['msg'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }

    if($new_password !== $confirm_password){
        $response['msg'] = 'New password and confirmation do not match.';
        echo json_encode($response);
        exit;
    }

    // Get current user id from session
    $user_id = $_SESSION['login_id'] ?? 0;
    if(!$user_id){
        $response['msg'] = 'User not logged in.';
        echo json_encode($response);
        exit;
    }

    // Fetch user hashed password from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if(!$hashed_password){
        $response['msg'] = 'User not found.';
        echo json_encode($response);
        exit;
    }

    // Verify current password using password_verify
    if(!password_verify($current_password, $hashed_password)){
        $response['msg'] = 'Current password is incorrect.';
        echo json_encode($response);
        exit;
    }

    // Hash new password
    $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // Update DB with new hashed password
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_hashed, $user_id);

    if($update_stmt->execute()){
        $response = ['status' => 'success', 'msg' => 'Password updated successfully.'];
    } else {
        $response['msg'] = 'Failed to update password.';
    }

    $update_stmt->close();
    echo json_encode($response);
    exit;
}

ob_end_flush();
?>
