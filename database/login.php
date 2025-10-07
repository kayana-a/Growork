<?php
require 'db.php';

header('Content-Type: application/json');

$email = $_POST['email'];
$password = $_POST['password'];

// 1. Validasi input (prevent empty/null injection)
if (!$email || !$password) {
  echo json_encode(['success' => false, 'message' => 'Email and password required']);
  exit;
}

// 2. Ambil user berdasarkan email
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// 3. Cek user dan password (jika hash, gunakan password_verify)
if (!$user || $user['password'] !== $password) {
  echo json_encode(['success' => false, 'message' => 'Email or password incorrect']);
  exit;
}

// 4. (Opsional) Set session login
// session_start();
// $_SESSION['user'] = $user['email'];

echo json_encode(['success' => true, 'message' => 'Login successful']);
