<?php
// pages/kategori.php

block_kategori_akses();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$role = $_SESSION['user_role'] ?? '';
$can_manage = true;

if ($action == 'hapus' && isset($_GET['id'])) {
    block_kategori_manage($role);
    $id = (int) $_GET['id'];

    $cek = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM dokumen WHERE kategori_id=$id");
    $in_use = (int) mysqli_fetch_assoc($cek)['total'];

    if ($in_use > 0) {
        flash_dokumen('danger', 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $in_use . ' dokumen.');
    } else {
        mysqli_query($koneksi, "DELETE FROM kategori WHERE id=$id");
        catat_log($koneksi, $_SESSION['user_id'], 'Menghapus Kategori ID: ' . $id);
        flash_dokumen('success', 'Kategori berhasil dihapus.');
    }

    header('Location: index.php?page=kategori');
    exit;
}

if ($action == 'tambah_proses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    block_kategori_manage($role);

    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '') {
        flash_dokumen('danger', 'Nama kategori wajib diisi.');
        header('Location: index.php?page=kategori&action=tambah');
        exit;
    }

    $nama_esc = mysqli_real_escape_string($koneksi, $nama);
    $desk_esc = mysqli_real_escape_string($koneksi, $deskripsi);

    $cek = mysqli_query($koneksi, "SELECT id FROM kategori WHERE nama='$nama_esc'");
    if (mysqli_num_rows($cek) > 0) {
        flash_dokumen('danger', 'Nama kategori sudah ada.');
        header('Location: index.php?page=kategori&action=tambah');
        exit;
    }

    mysqli_query($koneksi, "INSERT INTO kategori (nama, deskripsi) VALUES ('$nama_esc', '$desk_esc')");
    catat_log($koneksi, $_SESSION['user_id'], 'Menambahkan Kategori: ' . $nama);
    flash_dokumen('success', 'Kategori berhasil ditambahkan.');
    header('Location: index.php?page=kategori');
    exit;
}

if ($action == 'edit_proses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    block_kategori_manage($role);

    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '') {
        flash_dokumen('danger', 'Nama kategori wajib diisi.');
        header("Location: index.php?page=kategori&action=edit&id=$id");
        exit;
    }

    $nama_esc = mysqli_real_escape_string($koneksi, $nama);
    $desk_esc = mysqli_real_escape_string($koneksi, $deskripsi);

    $cek = mysqli_query($koneksi, "SELECT id FROM kategori WHERE nama='$nama_esc' AND id != $id");
    if (mysqli_num_rows($cek) > 0) {
        flash_dokumen('danger', 'Nama kategori sudah digunakan.');
        header("Location: index.php?page=kategori&action=edit&id=$id");
        exit;
    }

    mysqli_query($koneksi, "UPDATE kategori SET nama='$nama_esc', deskripsi='$desk_esc' WHERE id=$id");
    catat_log($koneksi, $_SESSION['user_id'], 'Mengubah Kategori ID: ' . $id);
    flash_dokumen('success', 'Kategori berhasil diperbarui.');
    header('Location: index.php?page=kategori');
    exit;
}

if (in_array($action, ['tambah', 'edit'], true)) {
    block_kategori_manage($role);
}
?>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">

                <?php if ($action == 'list'):
                    $colspan = $can_manage ? 5 : 4;
                    $daftar_kategori = ambil_daftar_kategori($koneksi, '', 'terbanyak');
                ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="card-title fw-semibold mb-0">Manajemen Kategori</h5>
                    <a href="index.php?page=kategori&action=tambah" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Kategori
                    </a>
                </div>
                <div class="list-filter-toolbar mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-5">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="kategoriSearch" placeholder="Cari kategori dokumen..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-8 col-md-6 col-lg-4">
                            <label class="form-label">Urutkan</label>
                            <select class="form-select" id="kategoriSort">
                                <option value="terbanyak">Dokumen Terbanyak</option>
                                <option value="tersedikit">Dokumen Tersedikit</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-2 col-lg-3">
                            <button type="button" class="btn btn-outline-secondary w-100" id="kategoriResetFilter">
                                <i class="ti ti-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="list-filter-meta mt-2">
                        <span class="list-filter-count" id="kategoriCount"></span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle table-bordered table-hover" id="tableKategori">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Kategori</th>
                                <th class="d-none d-md-table-cell">Deskripsi</th>
                                <th class="d-none d-md-table-cell text-center">Jumlah Dokumen</th>
                                <?php if ($can_manage): ?><th>Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="kategoriTableBody"
                               data-can-manage="<?= $can_manage ? '1' : '0' ?>"
                               data-colspan="<?= $colspan ?>">
                            <?php
                            if (count($daftar_kategori) > 0) {
                                $no = 1;
                                foreach ($daftar_kategori as $row):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['nama']) ?></h6>
                                    <div class="d-md-none mt-2 text-muted" style="font-size:12px;">
                                        <?= htmlspecialchars($row['deskripsi'] ?: '-') ?>
                                        <span class="badge bg-light text-dark border ms-1"><?= (int) $row['jml_dokumen'] ?> dokumen</span>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['deskripsi'] ?: '-') ?></td>
                                <td class="d-none d-md-table-cell text-center">
                                    <span class="badge bg-primary rounded-3"><?= (int) $row['jml_dokumen'] ?></span>
                                </td>
                                <?php if ($can_manage): ?>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="index.php?page=kategori&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-nowrap">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>
                                        <a href="index.php?page=kategori&action=hapus&id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-danger text-nowrap"
                                           onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="ti ti-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                                endforeach;
                            } else {
                                echo '<tr><td colspan="' . $colspan . '" class="text-center text-muted py-4">Belum ada kategori.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <script src="assets/js/kategori-list.js"></script>

                <?php elseif ($action == 'tambah'): ?>                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">Tambah Kategori</h5>
                    <a href="index.php?page=kategori" class="btn btn-outline-dark">Kembali</a>
                </div>

                <form action="index.php?page=kategori&action=tambah_proses" method="POST" class="mx-auto" style="max-width: 640px;">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" required maxlength="100" placeholder="Contoh: Dokumen Perencanaan">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Deskripsi <span class="text-muted">(opsional)</span></label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Keterangan singkat kategori"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 w-md-auto py-2 px-4 shadow-sm">
                        <i class="ti ti-device-floppy"></i> Simpan Kategori
                    </button>
                </form>

                <?php
                elseif ($action == 'edit' && isset($_GET['id'])):
                    $id = (int) $_GET['id'];
                    $edit_query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id=$id");
                    $edit_data = mysqli_fetch_assoc($edit_query);

                    if (!$edit_data):
                        echo '<div class="alert alert-danger">Data kategori tidak ditemukan.</div>';
                    else:
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">Edit Kategori</h5>
                    <a href="index.php?page=kategori" class="btn btn-outline-dark">Kembali</a>
                </div>

                <form action="index.php?page=kategori&action=edit_proses" method="POST" class="mx-auto" style="max-width: 640px;">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" required maxlength="100" value="<?= htmlspecialchars($edit_data['nama']) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Deskripsi <span class="text-muted">(opsional)</span></label>
                        <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 w-md-auto py-2 px-4 shadow-sm">
                        <i class="ti ti-device-floppy"></i> Simpan Perubahan
                    </button>
                </form>
                <?php
                    endif;
                endif;
                ?>

            </div>
        </div>
    </div>
</div>
