<?php
// 🔧 Tampilkan error (mode dev)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require 'db.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // pastikan path ke vendor benar

// Ambil email
$email = $_POST['email'] ?? '';

if (!$email) {
  echo json_encode(['success' => false, 'message' => 'Email tidak boleh kosong']);
  exit;
}

// Cek email di DB
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
  echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan']);
  exit;
}

// Buat OTP & expiry
$otp = strval(random_int(100000, 999999));
$expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Simpan OTP ke DB
$update = $pdo->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
$update->execute([$otp, $expiry, $email]);

// Konfigurasi PHPMailer
$mail = new PHPMailer(true);

try {
  // Konfigurasi SMTP Gmail
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'emailkamu@gmail.com'; // Ganti dengan email kamu
  $mail->Password = 'abcd efgh ijkl mnop'; // App Password dari Gmail
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = 587;

  // Pengirim & penerima
  $mail->setFrom('emailkamu@gmail.com', 'Growork Support');
  $mail->addAddress($email, $user['name'] ?? '');

  // Isi email
  $mail->isHTML(true);
  $mail->Subject = 'Your OTP Code - Growork';
  $mail->Body = "
    <h2>Hello, {$user['name']} 👋</h2>
    <p>Your One-Time Password (OTP) is:</p>
    <h1 style='color:#7b5fff;'>{$otp}</h1>
    <p>This OTP will expire in 5 minutes.</p>
    <br>
    <p>Best regards,<br><strong>Growork Team</strong></p>
  ";

  // Kirim email
  $mail->send();

  echo json_encode([
    'success' => true,
    'message' => 'OTP berhasil dikirim ke email kamu!'
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Gagal mengirim OTP: ' . $mail->ErrorInfo
  ]);
}
