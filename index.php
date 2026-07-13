<?php
session_start();
ob_start(); // Memulai output buffering untuk mencegah error "headers already sent"
require_once 'config/database.php';
require_once 'config/helper.php';

// Pastikan user sudah login
cek_login();

// Bagian Header dan Sidebar
include 'layout/header.php';
include 'layout/sidebar.php';
include 'layout/topbar.php';

// Menentukan halaman yang akan dimuat berdasarkan parameter 'page'
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Routing sederhana menggunakan switch-case
switch ($page) {
    case 'dashboard':
        include 'pages/dashboard.php';
        break;
    case 'dokumen':
        include 'pages/dokumen.php';
        break;
    case 'kategori':
        include 'pages/kategori.php';
        break;
    case 'pengguna':
        include 'pages/pengguna.php';
        break;
    case 'log_aktivitas':
        include 'pages/log_aktivitas.php';
        break;
    default:
        echo "<div class='alert alert-danger'>Halaman tidak ditemukan!</div>";
        break;
}

// Bagian Footer
include 'layout/footer.php';
?>
