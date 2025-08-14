<?php
session_start();
include 'db_connect.php';

$username = 'Jovic2025'; // replace with exact username in DB
$password = 'Jovic@2025';          // your test password

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$qry = $stmt->get_result();

if ($qry->num_rows === 0) {
    echo "User not found\n";
    exit;
}

$user = $qry->fetch_assoc();
$hashed = $user['password'];

if (password_verify($password, $hashed)) {
    echo "✅ Password matches\n";
} else {
    echo "❌ Password does not match\n";
}
