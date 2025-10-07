<?php
require 'db.php';

header('Content-Type: application/json');

$email = $_POST['email'];
$inputOtp = $_POST['otp'];

$stmt = $pdo->prepare("SELECT otp, otp_expiry FROM users WHERE LOWER(email) = LOWER(?)");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
  echo json_encode(['success' => false, 'message' => 'User not found']);
  exit;
}

if ($user['otp'] !== $inputOtp || strtotime($user['otp_expiry']) < time()) {
  echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
  exit;
}

// Bersihkan OTP agar tidak bisa dipakai ulang
$stmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE email = ?");
$stmt->execute([$email]);

echo json_encode(['success' => true]);
