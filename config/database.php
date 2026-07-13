<?php
// config/database.php

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP/Laragon password is usually empty
$db   = 'db_pendekar';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set timezone ke WIB (Waktu Indonesia Barat) untuk PHP dan MySQL
date_default_timezone_set('Asia/Jakarta');
mysqli_query($koneksi, "SET time_zone = '+07:00'");
?>
