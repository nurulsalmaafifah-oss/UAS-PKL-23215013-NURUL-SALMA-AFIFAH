<?php
// pages/dokumen.php

cek_role(['admin', 'staff', 'struktural']);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$role = $_SESSION['user_role'] ?? '';
$can_manage = dokumen_can_manage($role);

if ($action == 'hapus' && isset($_GET['id'])) {
    block_dokumen_manage($role);
    $id = (int) $_GET['id'];

    $q = mysqli_query($koneksi, "SELECT file_path, judul FROM dokumen WHERE id=$id");
    if ($r = mysqli_fetch_assoc($q)) {
        hapus_file_dokumen($r['file_path']);
        mysqli_query($koneksi, "DELETE FROM dokumen WHERE id=$id");
        catat_log($koneksi, $_SESSION['user_id'], 'Menghapus Dokumen: ' . $r['judul']);
        flash_dokumen('success', 'Dokumen berhasil dihapus.');
    }

    header('Location: index.php?page=dokumen');
    exit;
}

if ($action == 'tambah_proses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    block_dokumen_manage($role);

    $judul = mysqli_real_escape_string($koneksi, trim($_POST['judul'] ?? ''));
    $kategori_id = (int) ($_POST['kategori_id'] ?? 0);

    if ($judul === '') {
        flash_dokumen('danger', 'Judul dokumen wajib diisi.');
        header('Location: index.php?page=dokumen&action=tambah');
        exit;
    }

    if ($kategori_id <= 0) {
        flash_dokumen('danger', 'Kategori dokumen wajib dipilih.');
        header('Location: index.php?page=dokumen&action=tambah');
        exit;
    }

    $cek_kat = mysqli_query($koneksi, "SELECT id FROM kategori WHERE id=$kategori_id");
    if (mysqli_num_rows($cek_kat) === 0) {
        flash_dokumen('danger', 'Kategori tidak valid.');
        header('Location: index.php?page=dokumen&action=tambah');
        exit;
    }

    $error_file = validasi_upload_pdf($_FILES['file_dokumen'] ?? []);
    if ($error_file) {
        flash_dokumen('danger', $error_file);
        header('Location: index.php?page=dokumen&action=tambah');
        exit;
    }

    $target_file = simpan_file_dokumen($_FILES['file_dokumen']);
    if (!$target_file) {
        flash_dokumen('danger', 'Gagal menyimpan file. Silakan coba lagi.');
        header('Location: index.php?page=dokumen&action=tambah');
        exit;
    }

    $diunggah_oleh = (int) $_SESSION['user_id'];
    $tanggal_upload = date('Y-m-d H:i:s');
    $target_esc = mysqli_real_escape_string($koneksi, $target_file);

    mysqli_query($koneksi, "
        INSERT INTO dokumen (judul, file_path, kategori_id, diunggah_oleh, tanggal_upload)
        VALUES ('$judul', '$target_esc', $kategori_id, $diunggah_oleh, '$tanggal_upload')
    ");
    catat_log($koneksi, $_SESSION['user_id'], 'Mengunggah Dokumen: ' . $judul);
    flash_dokumen('success', 'Dokumen berhasil ditambahkan.');
    header('Location: index.php?page=dokumen');
    exit;
}

if ($action == 'edit_proses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    block_dokumen_manage($role);

    $id = (int) ($_POST['id'] ?? 0);
    $judul = mysqli_real_escape_string($koneksi, trim($_POST['judul'] ?? ''));
    $kategori_id = (int) ($_POST['kategori_id'] ?? 0);

    $q = mysqli_query($koneksi, "SELECT * FROM dokumen WHERE id=$id");
    if (mysqli_num_rows($q) === 0) {
        flash_dokumen('danger', 'Dokumen tidak ditemukan.');
        header('Location: index.php?page=dokumen');
        exit;
    }
    $dokumen_lama = mysqli_fetch_assoc($q);

    if ($judul === '') {
        flash_dokumen('danger', 'Judul dokumen wajib diisi.');
        header("Location: index.php?page=dokumen&action=edit&id=$id");
        exit;
    }

    if ($kategori_id <= 0) {
        flash_dokumen('danger', 'Kategori dokumen wajib dipilih.');
        header("Location: index.php?page=dokumen&action=edit&id=$id");
        exit;
    }

    $file_path = $dokumen_lama['file_path'];
    $has_new_file = isset($_FILES['file_dokumen']['error']) && $_FILES['file_dokumen']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($has_new_file) {
        $error_file = validasi_upload_pdf($_FILES['file_dokumen']);
        if ($error_file) {
            flash_dokumen('danger', $error_file);
            header("Location: index.php?page=dokumen&action=edit&id=$id");
            exit;
        }

        $target_file = simpan_file_dokumen($_FILES['file_dokumen']);
        if (!$target_file) {
            flash_dokumen('danger', 'Gagal menyimpan file baru.');
            header("Location: index.php?page=dokumen&action=edit&id=$id");
            exit;
        }

        hapus_file_dokumen($file_path);
        $file_path = $target_file;
    }

    $file_esc = mysqli_real_escape_string($koneksi, $file_path);
    mysqli_query($koneksi, "
        UPDATE dokumen
        SET judul='$judul', kategori_id=$kategori_id, file_path='$file_esc'
        WHERE id=$id
    ");
    catat_log($koneksi, $_SESSION['user_id'], 'Mengubah Dokumen: ' . $judul);
    flash_dokumen('success', 'Dokumen berhasil diperbarui.');
    header('Location: index.php?page=dokumen');
    exit;
}

if (in_array($action, ['tambah', 'edit'], true)) {
    block_dokumen_manage($role);
}
?>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">

                <?php if ($action == 'list'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">Data Dokumen</h5>
                    <?php if ($can_manage): ?>
                    <a href="index.php?page=dokumen&action=tambah" class="btn btn-primary">
                        <i class="ti ti-cloud-upload"></i> Unggah Dokumen
                    </a>
                    <?php endif; ?>
                </div>
                <div class="list-filter-toolbar mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="dokumenSearch" placeholder="Cari judul dokumen atau kategori...">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" id="dokumenFilterKategori">
                                <option value="">Semua Kategori</option>
                                <?php
                                $kat_filter = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY nama ASC");
                                while ($kf = mysqli_fetch_assoc($kat_filter)) {
                                    echo '<option value="' . (int) $kf['id'] . '">' . htmlspecialchars($kf['nama']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="form-label">Tanggal Upload</label>
                            <input type="date" class="form-control" id="dokumenFilterTanggal">
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <button type="button" class="btn btn-outline-secondary w-100" id="dokumenResetFilter">
                                <i class="ti ti-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="list-filter-meta mt-2">
                        <span class="list-filter-count" id="dokumenCount"></span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle table-bordered table-hover" id="tableDokumen">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th>No</th>
                                <th>Judul Dokumen</th>
                                <th class="d-none d-md-table-cell">Kategori</th>
                                <th class="d-none d-md-table-cell">Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "
                                SELECT d.*, k.nama AS nama_kategori
                                FROM dokumen d
                                LEFT JOIN kategori k ON d.kategori_id = k.id
                                ORDER BY d.id DESC
                            ");
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)):
                                    $nama_kategori = $row['nama_kategori'] ?: 'Tanpa Kategori';
                                    $search_blob = strtolower($row['judul'] . ' ' . $nama_kategori);
                            ?>
                            <tr class="list-data-row"
                                data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES) ?>"
                                data-kategori="<?= (int) $row['kategori_id'] ?>"
                                data-date="<?= date('Y-m-d', strtotime($row['tanggal_upload'])) ?>">
                                <td><?= $no++ ?></td>
                                <td>
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['judul']) ?></h6>
                                    <div class="d-md-none mt-2">
                                        <span class="badge bg-secondary" style="font-size: 10px;"><?= htmlspecialchars($nama_kategori) ?></span>
                                        <small class="text-muted d-block mt-1"><?= date('d M Y H:i', strtotime($row['tanggal_upload'])) ?></small>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell"><?= htmlspecialchars($nama_kategori) ?></td>
                                <td class="d-none d-md-table-cell"><?= date('d M Y H:i', strtotime($row['tanggal_upload'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <a href="index.php?page=dokumen&action=lihat&id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-nowrap">
                                            <i class="ti ti-eye"></i> Lihat
                                        </a>
                                        <?php if ($can_manage): ?>
                                        <a href="index.php?page=dokumen&action=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-nowrap">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>
                                        <a href="index.php?page=dokumen&action=hapus&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger text-nowrap" onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                                            <i class="ti ti-trash"></i> Hapus
                                        </a>
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
                <div class="list-pagination-wrap" id="dokumenPagination"></div>

                <script>
                window.addEventListener('load', function() {
                    if (document.getElementById('tableDokumen')) {
                        new PendekarListFilter({
                            table: '#tableDokumen',
                            searchInput: '#dokumenSearch',
                            resetBtn: '#dokumenResetFilter',
                            countEl: '#dokumenCount',
                            paginationEl: '#dokumenPagination',
                            perPage: 10,
                            emptyColspan: 5,
                            emptyMessage: 'Data tidak ditemukan',
                            filters: [
                                { el: '#dokumenFilterKategori', key: 'kategori' },
                                { el: '#dokumenFilterTanggal', key: 'date', match: 'date' }
                            ]
                        });
                    }
                });
                </script>

                <?php elseif ($action == 'tambah'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">Unggah Dokumen Baru</h5>
                    <a href="index.php?page=dokumen" class="btn btn-outline-dark">Kembali</a>
                </div>

                <form action="index.php?page=dokumen&action=tambah_proses" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 640px;">
                    <div class="mb-3">
                        <label class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" required maxlength="255" placeholder="Masukkan judul dokumen">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" name="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kat_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama ASC");
                            while ($kat = mysqli_fetch_assoc($kat_query)) {
                                echo '<option value="' . (int) $kat['id'] . '">' . htmlspecialchars($kat['nama']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">File Dokumen (PDF) <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="file_dokumen" accept=".pdf,application/pdf" required>
                        <div class="form-text mt-2">Format PDF saja. Ukuran maksimal 10 MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 w-md-auto py-2 px-4 shadow-sm">
                        <i class="ti ti-cloud-upload"></i> Unggah Dokumen
                    </button>
                </form>

                <?php elseif ($action == 'edit' && isset($_GET['id'])):
                    $id = (int) $_GET['id'];
                    $edit_query = mysqli_query($koneksi, "
                        SELECT d.*, k.nama AS nama_kategori
                        FROM dokumen d
                        LEFT JOIN kategori k ON d.kategori_id = k.id
                        WHERE d.id = $id
                    ");
                    $edit_data = mysqli_fetch_assoc($edit_query);

                    if (!$edit_data):
                        echo '<div class="alert alert-danger">Dokumen tidak ditemukan.</div>';
                    else:
                ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">Edit Dokumen</h5>
                    <a href="index.php?page=dokumen" class="btn btn-outline-dark">Kembali</a>
                </div>

                <form action="index.php?page=dokumen&action=edit_proses" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 640px;">
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" required maxlength="255" value="<?= htmlspecialchars($edit_data['judul']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" name="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kat_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama ASC");
                            while ($kat = mysqli_fetch_assoc($kat_query)) {
                                $sel = ((int) $kat['id'] === (int) $edit_data['kategori_id']) ? 'selected' : '';
                                echo '<option value="' . (int) $kat['id'] . '" ' . $sel . '>' . htmlspecialchars($kat['nama']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Saat Ini</label>
                        <div class="p-3 bg-light rounded border small text-muted">
                            <i class="ti ti-file-type-pdf me-1"></i>
                            <?= htmlspecialchars(basename($edit_data['file_path'])) ?>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Ganti File (PDF) <span class="text-muted">(opsional)</span></label>
                        <input class="form-control" type="file" name="file_dokumen" accept=".pdf,application/pdf">
                        <div class="form-text mt-2">Kosongkan jika tidak ingin mengganti file. Format PDF, maks. 10 MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 w-md-auto py-2 px-4 shadow-sm">
                        <i class="ti ti-device-floppy"></i> Simpan Perubahan
                    </button>
                </form>
                <?php
                    endif;

                elseif ($action == 'lihat' && isset($_GET['id'])):
                    $id = (int) $_GET['id'];
                    $query_detail = mysqli_query($koneksi, "
                        SELECT d.*, k.nama AS nama_kategori
                        FROM dokumen d
                        LEFT JOIN kategori k ON d.kategori_id = k.id
                        WHERE d.id = $id
                    ");
                    $dokumen = mysqli_fetch_assoc($query_detail);

                    if (!$dokumen):
                        echo '<div class="alert alert-danger">Dokumen tidak ditemukan.</div>';
                    else:
                        $file_ext = strtolower(pathinfo($dokumen['file_path'], PATHINFO_EXTENSION));
                ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 doc-view-header">
                    <div>
                        <h5 class="card-title fw-semibold mb-1"><?= htmlspecialchars($dokumen['judul']) ?></h5>
                        <span class="badge bg-secondary"><?= htmlspecialchars($dokumen['nama_kategori'] ?: 'Tanpa Kategori') ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= htmlspecialchars($dokumen['file_path']) ?>" download class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-download"></i> Unduh
                        </a>
                        <a href="index.php?page=dokumen" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="doc-preview-full">
                    <?php if ($file_ext === 'pdf'): ?>
                    <iframe src="<?= htmlspecialchars($dokumen['file_path']) ?>" title="<?= htmlspecialchars($dokumen['judul']) ?>"></iframe>
                    <?php else: ?>
                    <div class="doc-preview-fallback">
                        <i class="ti ti-file-off"></i>
                        <h5>Preview tidak tersedia</h5>
                        <p class="text-muted mb-3">Dokumen lama mungkin bukan format PDF. Silakan unduh untuk melihat isinya.</p>
                        <a href="<?= htmlspecialchars($dokumen['file_path']) ?>" download class="btn btn-primary">
                            <i class="ti ti-download"></i> Unduh Dokumen
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php
                    endif;
                endif;
                ?>

            </div>
        </div>
    </div>
</div>
