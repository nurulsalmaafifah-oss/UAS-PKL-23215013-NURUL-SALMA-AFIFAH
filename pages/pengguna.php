<?php
// pages/pengguna.php

// Hanya admin yang bisa akses
cek_role('admin');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Proses Hapus
if ($action == 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Jangan izinkan admin menghapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Anda tidak bisa menghapus akun Anda sendiri.'); window.location='index.php?page=pengguna';</script>";
        exit;
    }
    
    mysqli_query($koneksi, "DELETE FROM pengguna WHERE id=$id");
    catat_log($koneksi, $_SESSION['user_id'], 'Menghapus Pengguna ID: '.$id);
    header("Location: index.php?page=pengguna");
    exit;
}

// Proses Tambah
if ($action == 'tambah_proses' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_plain = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    $error_pwd = validasi_password_kuat($password_plain);
    if ($error_pwd) {
        pendekar_flash('danger', $error_pwd);
        header('Location: index.php?page=pengguna&action=tambah');
        exit;
    }

    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    
    // Cek username unik
    $cek = mysqli_query($koneksi, "SELECT id FROM pengguna WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah terdaftar!'); window.location='index.php?page=pengguna&action=tambah';</script>";
        exit;
    }
    
    mysqli_query($koneksi, "INSERT INTO pengguna (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");
    catat_log($koneksi, $_SESSION['user_id'], 'Menambahkan Akun Pengguna: '.$username);
    header("Location: index.php?page=pengguna");
    exit;
}

// Proses Edit
if ($action == 'edit_proses' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    
    // Cek username unik (kecuali username sendiri)
    $cek = mysqli_query($koneksi, "SELECT id FROM pengguna WHERE username='$username' AND id != $id");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah terdaftar oleh pengguna lain!'); window.location='index.php?page=pengguna&action=edit&id=$id';</script>";
        exit;
    }
    
    // Jika password diisi, maka update password juga
    if (!empty($_POST['password'])) {
        $error_pwd = validasi_password_kuat($_POST['password']);
        if ($error_pwd) {
            pendekar_flash('danger', $error_pwd);
            header("Location: index.php?page=pengguna&action=edit&id=$id");
            exit;
        }
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE pengguna SET nama='$nama', username='$username', password='$password', role='$role' WHERE id=$id");
    } else {
        mysqli_query($koneksi, "UPDATE pengguna SET nama='$nama', username='$username', role='$role' WHERE id=$id");
    }
    
    catat_log($koneksi, $_SESSION['user_id'], 'Mengubah Akun Pengguna ID: '.$id);
    header("Location: index.php?page=pengguna");
    exit;
}
?>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                
                <?php if ($action == 'list'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Manajemen Pengguna</h5>
                    <a href="index.php?page=pengguna&action=tambah" class="btn btn-primary"><i class="ti ti-user-plus"></i> Tambah Pengguna</a>
                </div>

                <div class="list-filter-toolbar mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-5">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="penggunaSearch" placeholder="Cari nama pengguna atau role...">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" id="penggunaFilterRole">
                                <option value="">Semua Role</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="struktural">Struktural</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <button type="button" class="btn btn-outline-secondary w-100" id="penggunaResetFilter">
                                <i class="ti ti-refresh"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                    <div class="list-filter-meta mt-2">
                        <span class="list-filter-count" id="penggunaCount"></span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle table-bordered table-hover" id="tablePengguna">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th>No</th>
                                <th>Pengguna</th>
                                <th class="d-none d-md-table-cell">Username</th>
                                <th class="d-none d-md-table-cell">Hak Akses (Role)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "SELECT * FROM pengguna ORDER BY id DESC");
                            if (mysqli_num_rows($query) > 0) {
                                while($row = mysqli_fetch_assoc($query)):
                                    $search_blob = strtolower(
                                        $row['nama'] . ' ' . $row['username'] . ' ' . $row['role']
                                    );
                            ?>
                            <tr class="list-data-row"
                                data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES) ?>"
                                data-role="<?= htmlspecialchars($row['role'], ENT_QUOTES) ?>">
                                <td><?= $no++ ?></td>
                                <td>
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['nama']) ?></h6>
                                    <div class="d-md-none mt-2">
                                        <small class="text-muted d-block">@<?= htmlspecialchars($row['username']) ?></small>
                                        <?php 
                                            if($row['role'] == 'admin') echo '<span class="badge bg-danger mt-1" style="font-size:10px;">Admin</span>';
                                            elseif($row['role'] == 'staff') echo '<span class="badge bg-primary mt-1" style="font-size:10px;">Staff</span>';
                                            else echo '<span class="badge bg-info mt-1" style="font-size:10px;">Struktural</span>';
                                        ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['username']) ?></td>
                                <td class="d-none d-md-table-cell">
                                    <?php 
                                        if($row['role'] == 'admin') echo '<span class="badge bg-danger rounded-3 fw-semibold">Admin</span>';
                                        elseif($row['role'] == 'staff') echo '<span class="badge bg-primary rounded-3 fw-semibold">Staff</span>';
                                        else echo '<span class="badge bg-info rounded-3 fw-semibold">Struktural</span>';
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="index.php?page=pengguna&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-nowrap"><i class="ti ti-edit"></i> Edit</a>
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="index.php?page=pengguna&action=hapus&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger text-nowrap" onclick="return confirm('Yakin ingin menghapus akun ini?')"><i class="ti ti-trash"></i> Hapus</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="list-pagination-wrap" id="penggunaPagination"></div>

                <script>
                window.addEventListener('load', function() {
                    if (document.getElementById('tablePengguna')) {
                        new PendekarListFilter({
                            table: '#tablePengguna',
                            searchInput: '#penggunaSearch',
                            resetBtn: '#penggunaResetFilter',
                            countEl: '#penggunaCount',
                            paginationEl: '#penggunaPagination',
                            perPage: 10,
                            emptyColspan: 5,
                            emptyMessage: 'Data tidak ditemukan',
                            filters: [
                                { el: '#penggunaFilterRole', key: 'role' }
                            ]
                        });
                    }
                });
                </script>

                <?php elseif ($action == 'tambah'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Tambah Pengguna</h5>
                    <a href="index.php?page=pengguna" class="btn btn-outline-dark">Kembali</a>
                </div>
                
                <form action="index.php?page=pengguna&action=tambah_proses" method="POST" class="form-pendekar-password">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control pendekar-password-input" name="password" required minlength="8">
                        <div class="form-text mt-2"><?= PENDEKAR_PASSWORD_HINT ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="struktural">Struktural</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 w-md-auto py-2 px-4 shadow-sm">Simpan Pengguna</button>
                </form>

                <?php 
                elseif ($action == 'edit' && isset($_GET['id'])): 
                    $id = (int)$_GET['id'];
                    $edit_query = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id=$id");
                    $edit_data = mysqli_fetch_assoc($edit_query);
                    
                    if (!$edit_data) {
                        echo "<div class='alert alert-danger'>Data tidak ditemukan!</div>";
                    } else {
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Edit Pengguna</h5>
                    <a href="index.php?page=pengguna" class="btn btn-outline-secondary">Kembali</a>
                </div>
                
                <form action="index.php?page=pengguna&action=edit_proses" method="POST" class="form-pendekar-password">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($edit_data['nama']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($edit_data['username']) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control pendekar-password-input" name="password" minlength="8" placeholder="(Opsional) Kosongkan jika tidak ubah">
                            <div class="form-text mt-2"><?= PENDEKAR_PASSWORD_HINT ?></div>
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="admin" <?= $edit_data['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="staff" <?= $edit_data['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                                <option value="struktural" <?= $edit_data['role'] == 'struktural' ? 'selected' : '' ?>>Struktural</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 w-md-auto py-2 px-4 shadow-sm">Update Pengguna</button>
                </form>
                <?php 
                    }
                endif; 
                ?>
                
            </div>
        </div>
    </div>
</div>
