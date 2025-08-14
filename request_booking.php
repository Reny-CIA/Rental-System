<?php
session_start();
include('./db_connect.php');

if (!isset($_SESSION['login_id']) || $_SESSION['login_type'] != 'client') {
    echo "Unauthorized access.";
    exit;
}

$house_id = intval($_POST['house_id']);
$client_id = $_SESSION['login_id'];
$client_name = $_SESSION['login_name'];
$client_phone = $_SESSION['login_phone'];

// Get house details
$stmt = $conn->prepare("SELECT house_number, price FROM houses WHERE id = ?");
$stmt->bind_param("i", $house_id);
$stmt->execute();
$stmt->bind_result($house_number, $price);
$stmt->fetch();
$stmt->close();

// Send email to landlord
$landlord_email = "landlord@example.com"; // Replace with dynamic if you store per-house
$subject = "Booking Request for House $house_number";
$message = "Client $client_name (Phone: $client_phone) wants to book House $house_number, priced at $price.";
$headers = "From: noreply@yourdomain.com";
mail($landlord_email, $subject, $message, $headers);

// Store booking
$conn->query("INSERT INTO bookings (house_id, client_id) VALUES ($house_id, $client_id)");

echo "Booking request sent successfully!";
?>
