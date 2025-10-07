<?php
// 🔧 Tampilkan error kalau ada
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔐 Header untuk JSON
header('Content-Type: application/json');

// ✅ Koneksi DB
require 'db.php';

// ✅ Ambil email dari request
$email = $_POST['email'] ?? '';

if (!$email) {
  echo json_encode(['success' => false, 'message' => 'Email tidak boleh kosong']);
  exit;
}

// ✅ Cek apakah email ada di database
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
  echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan']);
  exit;
}

// 🔐 Generate OTP
$otp = rand(100000, 999999);
$expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// 💾 Simpan OTP & expiry ke DB
$update = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
$update->execute([$otp, $expiry, $email]);

// 🧪 Simulasi kirim email
echo json_encode([
  'success' => true,
  'message' => 'OTP berhasil dikirim (simulasi)',
  'otp' => $otp // bisa kamu sembunyikan di production
]);
