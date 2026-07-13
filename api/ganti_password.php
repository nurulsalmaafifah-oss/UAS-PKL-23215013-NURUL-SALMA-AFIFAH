<?php
// api/ganti_password.php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/helper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['konfirmasi_password'] ?? '';

if ($password_lama === '' || $password_baru === '' || $konfirmasi === '') {
    echo json_encode(['success' => false, 'message' => 'Semua field password wajib diisi.']);
    exit;
}

$error_pwd = validasi_password_kuat($password_baru);
if ($error_pwd) {
    echo json_encode(['success' => false, 'message' => $error_pwd]);
    exit;
}

if ($password_baru !== $konfirmasi) {
    echo json_encode(['success' => false, 'message' => 'Konfirmasi password tidak cocok.']);
    exit;
}

$query = mysqli_query($koneksi, "SELECT password FROM pengguna WHERE id = $user_id LIMIT 1");
if (!$query || mysqli_num_rows($query) === 0) {
    echo json_encode(['success' => false, 'message' => 'Data pengguna tidak ditemukan.']);
    exit;
}

$user = mysqli_fetch_assoc($query);
$hash_db = $user['password'];

$lama_valid = password_verify($password_lama, $hash_db);
if (!$lama_valid && $password_lama === $hash_db) {
    $lama_valid = true;
}

if (!$lama_valid) {
    echo json_encode(['success' => false, 'message' => 'Password lama tidak sesuai.']);
    exit;
}

if ($password_lama === $password_baru) {
    echo json_encode(['success' => false, 'message' => 'Password baru harus berbeda dari password lama.']);
    exit;
}

$hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
$hash_esc = mysqli_real_escape_string($koneksi, $hash_baru);

if (mysqli_query($koneksi, "UPDATE pengguna SET password = '$hash_esc' WHERE id = $user_id")) {
    catat_log($koneksi, $user_id, 'Mengganti password akun');
    echo json_encode(['success' => true, 'message' => 'Password berhasil diperbarui.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan password. Silakan coba lagi.']);
}
