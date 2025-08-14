<?php
// Disable all notices/warnings from being sent to the client
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always JSON
header('Content-Type: application/json; charset=utf-8');

ob_start();
include 'db_connect.php';
include 'admin_class.php';

$crud   = new Action();
$action = $_GET['action'] ?? '';

// Helper to require login
function require_login() {
    if (!isset($_SESSION['login_id'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Not logged in']);
        exit();
    }
}

switch ($action) {
    // Public actions
    case 'login':
        echo $crud->login('admin');
        break;

    case 'login2':
        echo $crud->login('tenant');
        break;

    case 'signup':
        echo $crud->signup();
        break;

    // Protected actions
    case 'logout':
        require_login();
        $crud->logout('login.php');
        break;

    case 'logout2':
        require_login();
        $crud->logout('../index.php');
        break;

    case 'save_user':
        require_login();
        echo $crud->save_user();
        break;

    case 'delete_user':
        require_login();
        echo $crud->delete_user();
        break;

    case 'update_account':
        require_login();
        echo $crud->update_account();
        break;

    case 'save_settings':
        require_login();
        echo $crud->save_settings();
        break;

    case 'save_category':
        require_login();
        echo $crud->save_category();
        break;

    case 'delete_category':
        require_login();
        echo $crud->delete_category();
        break;

    case 'save_house':
        require_login();
        echo $crud->save_house();
        break;

    case 'delete_house':
        require_login();
        echo $crud->delete_house();
        break;

    case 'save_tenant':
        require_login();
        echo $crud->save_tenant();
        break;

    case 'delete_tenant':
        require_login();
        echo $crud->delete_tenant();
        break;

    case 'get_tdetails':
        require_login();
        echo $crud->get_tdetails();
        break;

    case 'save_payment':
        require_login();
        echo $crud->save_payment();
        break;

    case 'delete_payment':
        require_login();
        echo $crud->delete_payment();
        break;

    // Extra endpoints
    case 'get_vacant_count':
        require_login();
        $vacantResult = $conn->query("
            SELECT h.id
            FROM houses h
            LEFT JOIN tenants t ON t.house_id = h.id AND t.status = 1
            GROUP BY h.id
            HAVING COUNT(t.id) = 0
        ");
        echo json_encode(['count' => $vacantResult->num_rows]);
        break;

    case 'get_arrears_count':
        require_login();
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
        echo json_encode(['count' => $arrearsResult->num_rows]);
        break;

    case 'change_password':
        require_login();
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(['status' => 'error', 'msg' => 'Please fill in all fields.']);
            break;
        }
        if ($new_password !== $confirm_password) {
            echo json_encode(['status' => 'error', 'msg' => 'New password and confirmation do not match.']);
            break;
        }

        $user_id = $_SESSION['login_id'];
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current_password, $hashed_password)) {
            echo json_encode(['status' => 'error', 'msg' => 'Current password is incorrect.']);
            break;
        }

        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_hashed, $user_id);

        if ($update_stmt->execute()) {
            echo json_encode(['status' => 'success', 'msg' => 'Password updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Failed to update password.']);
        }
        $update_stmt->close();
        break;

    case 'change_user_password':
        require_login();
        $id       = $_POST['id'] ?? 0;
        $password = $_POST['password'] ?? '';
        if (empty($id) || empty($password)) {
            echo json_encode(['status' => 'error', 'msg' => 'Missing user ID or password']);
            break;
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Database update failed']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
        break;
}

ob_end_flush();
