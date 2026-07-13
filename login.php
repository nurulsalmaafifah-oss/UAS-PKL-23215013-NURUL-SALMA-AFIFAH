<?php
session_start();
require_once 'config/database.php';
require_once 'config/helper.php';

// Jika sudah login, arahkan ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM pengguna WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password (Diberikan dukungan untuk plain text bagi data lama dari db_pendekar.sql)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];

            // Catat log
            require_once 'config/helper.php';
            catat_log($koneksi, $user['id'], 'Melakukan Login');

            header("Location: index.php");
            exit;
        } else {
            $error = "Username dan password salah!";
        }
    } else {
        $error = "Username dan password salah!";
    }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - PENDEKAR</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/logo_tegal.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <link rel="stylesheet" href="assets/css/pemerintahan.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="login-bg position-relative overflow-hidden min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card login-card mb-0">
              <div class="card-body">
                <a href="#" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="assets/images/logos/logo_tegal.png" alt="Logo Kabupaten Tegal" style="width: 90px; height: auto;" class="mb-1">
                  <h2 class="text-primary fw-bold mb-0">PENDEKAR </h2>
                  <!-- <img src="assets/images/logos/dark-logo.svg" width="180" alt=""> -->
                </a>
                <p class="text-center mb-3" >Penyimpanan Dokumen Elektronik Perkantoran</p>
                
                <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                  <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="username" required autofocus>
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-0 rounded-2">Masuk</button>
                </form>

                <footer class="login-card-footer">
                  <p class="login-footer-line1">© 2026 Sistem Pendekar</p>
                  <p class="login-footer-line2">Dinas Perpustakaan dan Kearsipan Kabupaten Tegal</p>
                </footer>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
