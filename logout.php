<?php
// logout.php
session_start();
require_once 'config/database.php';
require_once 'config/helper.php';

if (isset($_SESSION['user_id'])) {
    catat_log($koneksi, $_SESSION['user_id'], 'Melakukan Logout');
}

// Hapus semua session
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>
