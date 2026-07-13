<?php
// config/helper.php

define('DOKUMEN_MAX_FILE_SIZE', 10 * 1024 * 1024);
define('PENDEKAR_PASSWORD_MSG', 'Password harus terdiri dari minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta karakter khusus.');
define('PENDEKAR_PASSWORD_HINT', 'Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, serta karakter khusus.');
define('PENDEKAR_PASSWORD_SPECIAL_REGEX', '/[!@#$%^&*()_+\-=?.,]/');

/**
 * Mencatat log aktivitas pengguna ke database.
 */
function catat_log($koneksi, $pengguna_id, $aktivitas) {
    if (!$pengguna_id) return;

    $aktivitas = mysqli_real_escape_string($koneksi, $aktivitas);
    $waktu = date('Y-m-d H:i:s');

    mysqli_query($koneksi, "INSERT INTO log_aktivitas (pengguna_id, aktivitas, dibuat_pada) VALUES ('$pengguna_id', '$aktivitas', '$waktu')");
}

function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function cek_role($roles_allowed) {
    if (!isset($_SESSION['user_role'])) {
        header("Location: login.php");
        exit;
    }

    $current_role = $_SESSION['user_role'];

    if (is_array($roles_allowed)) {
        if (!in_array($current_role, $roles_allowed)) {
            echo "Anda tidak memiliki akses ke halaman ini.";
            exit;
        }
    } elseif ($current_role != $roles_allowed && $roles_allowed !== 'all') {
        echo "Anda tidak memiliki akses ke halaman ini.";
        exit;
    }
}

/**
 * Staff dapat mengelola dokumen; admin & struktural hanya melihat.
 */
function dokumen_can_manage($role = null) {
    $role = $role ?? ($_SESSION['user_role'] ?? '');
    return $role === 'staff';
}

function block_dokumen_manage($role = null) {
    if (!dokumen_can_manage($role)) {
        header("Location: index.php?page=dokumen");
        exit;
    }
}

/**
 * Hanya admin yang dapat mengakses menu & halaman kategori.
 */
function kategori_can_access($role = null) {
    $role = $role ?? ($_SESSION['user_role'] ?? '');
    return $role === 'admin';
}

function kategori_can_manage($role = null) {
    return kategori_can_access($role);
}

function block_kategori_akses($role = null) {
    if (!kategori_can_access($role)) {
        pendekar_flash('danger', 'Akses ditolak.');
        header('Location: index.php?page=dashboard');
        exit;
    }
}

function block_kategori_manage($role = null) {
    block_kategori_akses($role);
}

/**
 * Validasi kekuatan password.
 * @return string|null Pesan error atau null jika valid
 */
function validasi_password_kuat($password) {
    $password = (string) $password;
    if (strlen($password) < 8) {
        return PENDEKAR_PASSWORD_MSG;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return PENDEKAR_PASSWORD_MSG;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return PENDEKAR_PASSWORD_MSG;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return PENDEKAR_PASSWORD_MSG;
    }
    if (!preg_match(PENDEKAR_PASSWORD_SPECIAL_REGEX, $password)) {
        return PENDEKAR_PASSWORD_MSG;
    }
    return null;
}

/**
 * Ambil daftar kategori dengan pencarian & pengurutan (prepared statement).
 *
 * @param mysqli $koneksi
 * @param string $q Kata kunci (nama / deskripsi)
 * @param string $sort terbanyak|tersedikit
 * @return array
 */
function ambil_daftar_kategori($koneksi, $q = '', $sort = 'terbanyak') {
    $sort = ($sort === 'tersedikit') ? 'tersedikit' : 'terbanyak';
    $order = ($sort === 'tersedikit')
        ? 'jml_dokumen ASC, k.nama ASC'
        : 'jml_dokumen DESC, k.nama ASC';

    $sql = "
        SELECT k.id, k.nama, k.deskripsi,
               (SELECT COUNT(*) FROM dokumen d WHERE d.kategori_id = k.id) AS jml_dokumen
        FROM kategori k
    ";

    $q = trim((string) $q);
    if ($q !== '') {
        $sql .= " WHERE (k.nama LIKE ? OR IFNULL(k.deskripsi, '') LIKE ?)";
    }

    $sql .= " ORDER BY $order";

    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        return [];
    }

    if ($q !== '') {
        $like = '%' . $q . '%';
        mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $rows;
}

function jenis_aktivitas_log($aktivitas) {
    $a = strtolower($aktivitas);
    if (strpos($a, 'login') !== false || strpos($a, 'logout') !== false) {
        return 'login';
    }
    if (strpos($a, 'dokumen') !== false) {
        return 'dokumen';
    }
    if (strpos($a, 'pengguna') !== false || strpos($a, 'akun') !== false) {
        return 'pengguna';
    }
    return 'lainnya';
}

function label_role_pengguna($role) {
    $map = [
        'admin' => 'Administrator',
        'staff' => 'Staff',
        'struktural' => 'Struktural',
    ];
    $key = strtolower(trim((string) $role));
    return $map[$key] ?? ucfirst($key);
}

function badge_class_role($role) {
    $map = [
        'admin' => 'bg-danger',
        'staff' => 'bg-primary',
        'struktural' => 'bg-info',
    ];
    return $map[strtolower(trim((string) $role))] ?? 'bg-secondary';
}

function inisial_nama($nama) {
    $parts = preg_split('/\s+/', trim((string) $nama));
    if (count($parts) >= 2) {
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    }
    return strtoupper(mb_substr($nama, 0, 2));
}

/**
 * Validasi upload file PDF dokumen.
 * @return string|null Pesan error, atau null jika valid
 */
function validasi_upload_pdf($file) {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'File dokumen wajib diunggah.';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return 'Ukuran file melebihi batas yang diizinkan (maks. 10 MB).';
        }
        return 'Gagal mengunggah file. Silakan coba lagi.';
    }

    if ($file['size'] > DOKUMEN_MAX_FILE_SIZE) {
        return 'Ukuran file melebihi batas maksimal 10 MB.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return 'Format file tidak didukung. Hanya file PDF yang diizinkan.';
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            return 'File harus berformat PDF yang valid.';
        }
    }

    return null;
}

function simpan_file_dokumen($file) {
    $target_dir = 'uploads/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($file['name']));
    $target_file = $target_dir . $new_file_name;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        return null;
    }

    return $target_file;
}

function hapus_file_dokumen($file_path) {
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
    }
}

function pendekar_flash($type, $message) {
    $_SESSION['flash_pendekar'] = ['type' => $type, 'message' => $message];
}

function flash_dokumen($type, $message) {
    pendekar_flash($type, $message);
}

function tampilkan_pendekar_flash() {
    if (empty($_SESSION['flash_pendekar'])) {
        return;
    }
    $flash = $_SESSION['flash_pendekar'];
    unset($_SESSION['flash_pendekar']);

    $type = $flash['type'] ?? 'info';
    $class_map = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];
    $icon_map = [
        'success' => 'ti ti-circle-check',
        'danger' => 'ti ti-alert-circle',
        'warning' => 'ti ti-alert-triangle',
        'info' => 'ti ti-info-circle',
    ];
    $class = $class_map[$type] ?? 'alert-info';
    $icon = $icon_map[$type] ?? 'ti ti-info-circle';
    $auto_dismiss = ($type === 'success') ? ' data-auto-dismiss="5000"' : '';

    echo '<div class="alert pendekar-alert ' . $class . ' alert-dismissible fade show" role="alert"' . $auto_dismiss . '>'
        . '<i class="' . $icon . ' me-2"></i>'
        . htmlspecialchars($flash['message'] ?? '')
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>';
}

function tampilkan_flash_dokumen() {
    tampilkan_pendekar_flash();
}
?>
