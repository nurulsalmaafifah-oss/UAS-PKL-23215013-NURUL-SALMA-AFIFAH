<?php
// scripts/migrate_revisi_sistem.php - jalankan sekali setelah revisi sistem
require_once __DIR__ . '/../config/database.php';

function run_step($koneksi, $sql, $label) {
    if (!mysqli_query($koneksi, $sql)) {
        die("Gagal [$label]: " . mysqli_error($koneksi) . "\nSQL: $sql\n");
    }
    echo "OK: $label\n";
}

echo "=== Migrasi Revisi Sistem PENDEKAR ===\n\n";

// Hapus fitur disposisi
$tables = ['disposisi_balasan', 'disposisi', 'versi_dokumen'];
foreach ($tables as $table) {
    run_step($koneksi, "DROP TABLE IF EXISTS `$table`", "Drop table $table");
}

// Perbarui kategori dokumen
run_step($koneksi, "UPDATE dokumen SET kategori_id = NULL", "Reset referensi kategori dokumen");
run_step($koneksi, "DELETE FROM kategori", "Hapus kategori lama");

$kategori_baru = [
    ['Dokumen Perencanaan', 'Dokumen perencanaan kegiatan dan program'],
    ['Dokumen Laporan Keuangan', 'Dokumen laporan keuangan dan pertanggungjawaban'],
    ['Dokumen Bidang Kearsipan', 'Dokumen bidang kearsipan'],
    ['Dokumen Bidang Perpustakaan', 'Dokumen bidang perpustakaan'],
    ['Dokumen Pengadaan Barang dan Jasa', 'Dokumen pengadaan barang dan jasa'],
    ['Dokumen Rencana Kerja', 'Dokumen rencana kerja'],
];

foreach ($kategori_baru as $kat) {
    $nama = mysqli_real_escape_string($koneksi, $kat[0]);
    $desk = mysqli_real_escape_string($koneksi, $kat[1]);
    run_step($koneksi, "INSERT INTO kategori (nama, deskripsi) VALUES ('$nama', '$desk')", "Insert kategori: {$kat[0]}");
}

// Dokumen tanpa kategori -> Dokumen Rencana Kerja (id terakhir / id 6 setelah fresh insert)
$id_default = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM kategori WHERE nama = 'Dokumen Rencana Kerja' LIMIT 1"))['id'];
run_step($koneksi, "UPDATE dokumen SET kategori_id = $id_default WHERE kategori_id IS NULL", "Assign kategori default ke dokumen lama");

// Bersihkan kolom dokumen yang tidak dipakai (abaikan error jika sudah dihapus)
$drop_cols = ['nomor_dokumen', 'deskripsi', 'status'];
foreach ($drop_cols as $col) {
    $check = mysqli_query($koneksi, "SHOW COLUMNS FROM dokumen LIKE '$col'");
    if ($check && mysqli_num_rows($check) > 0) {
        run_step($koneksi, "ALTER TABLE dokumen DROP COLUMN `$col`", "Drop kolom dokumen.$col");
    }
}

// Pastikan tanggal_upload datetime
$check_tgl = mysqli_query($koneksi, "SHOW COLUMNS FROM dokumen LIKE 'tanggal_upload'");
if ($check_tgl && ($col = mysqli_fetch_assoc($check_tgl)) && stripos($col['Type'], 'datetime') === false) {
    run_step($koneksi, "ALTER TABLE dokumen MODIFY tanggal_upload DATETIME DEFAULT NULL", "Ubah tipe tanggal_upload ke DATETIME");
}

echo "\n--- Kategori saat ini ---\n";
$q = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY id");
while ($r = mysqli_fetch_assoc($q)) {
    echo $r['id'] . ' | ' . $r['nama'] . "\n";
}

echo "\n--- Dokumen ---\n";
$q = mysqli_query($koneksi, "SELECT d.id, d.judul, k.nama AS kategori FROM dokumen d LEFT JOIN kategori k ON d.kategori_id = k.id ORDER BY d.id DESC");
while ($r = mysqli_fetch_assoc($q)) {
    echo $r['id'] . ' | ' . $r['judul'] . ' | ' . ($r['kategori'] ?: '-') . "\n";
}

echo "\nMigrasi revisi sistem selesai.\n";
