<?php
// api/kategori_list.php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/helper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbanyak';

$data = ambil_daftar_kategori($koneksi, $q, $sort);

echo json_encode([
    'success' => true,
    'data' => $data,
    'total' => count($data),
    'can_manage' => kategori_can_manage($role),
]);
