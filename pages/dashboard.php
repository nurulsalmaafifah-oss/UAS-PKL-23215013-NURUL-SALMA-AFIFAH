<?php
// pages/dashboard.php

$user_nama = $_SESSION['user_nama'] ?? 'Pengguna';
$inisial = inisial_nama($user_nama);
$role = $_SESSION['user_role'] ?? '';

$total_dokumen = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM dokumen"))['total'];
$total_kategori = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kategori"))['total'];
$total_pengguna = (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna"))['total'];
?>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100 bg-light-info overflow-hidden shadow-none">
            <div class="card-body position-relative">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="d-flex align-items-center mb-7">
                            <div class="profile-avatar">
                                <span class="profile-initials"><?= htmlspecialchars($inisial) ?></span>
                            </div>
                            <div>
                                <h5 class="fw-semibold mb-1 fs-5">Selamat Datang kembali, <?= htmlspecialchars($user_nama) ?>!</h5>
                                <span class="badge <?= badge_class_role($role) ?>"><?= htmlspecialchars(label_role_pengguna($role)) ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            <div class="border-end pe-4 border-muted border-opacity-10">
                                <h3 class="mb-1 fw-semibold fs-8"><?= $total_dokumen ?></h3>
                                <p class="mb-0 text-dark">Total Dokumen</p>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-semibold fs-8"><?= $total_kategori ?></h3>
                                <p class="mb-0 text-dark">Kategori Dokumen</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="welcome-bg-img mb-n7 text-end">
                            <img src="assets/images/backgrounds/welcome-bg.svg" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6 col-lg-4 mb-3">
        <div class="card border-bottom border-primary card-hover h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h4 class="card-title fs-5">Dokumen</h4>
                        <p class="card-subtitle text-dark">Arsip Elektronik</p>
                    </div>
                    <div class="ms-auto">
                        <span class="text-primary display-6"><i class="ti ti-file-description"></i></span>
                    </div>
                </div>
                <div class="mt-3">
                    <h2 class="fs-8"><?= $total_dokumen ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 mb-3">
        <div class="card border-bottom border-success card-hover h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h4 class="card-title fs-5">Kategori</h4>
                        <p class="card-subtitle text-dark">Klasifikasi Dokumen</p>
                    </div>
                    <div class="ms-auto">
                        <span class="text-success display-6"><i class="ti ti-category"></i></span>
                    </div>
                </div>
                <div class="mt-3">
                    <h2 class="fs-8"><?= $total_kategori ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="col-12 col-md-6 col-lg-4 mb-3">
        <div class="card border-bottom border-danger card-hover h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h4 class="card-title fs-5">Pengguna</h4>
                        <p class="card-subtitle text-dark">Total Akun</p>
                    </div>
                    <div class="ms-auto">
                        <span class="text-danger display-6"><i class="ti ti-users"></i></span>
                    </div>
                </div>
                <div class="mt-3">
                    <h2 class="fs-8"><?= $total_pengguna ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <h5 class="card-title fw-semibold mb-4">Aktivitas Terbaru</h5>
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle">
                        <thead class="text-dark fs-4">
                            <tr class="table-light">
                                <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Waktu</h6></th>
                                <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Pengguna</h6></th>
                                <th class="border-bottom-0 d-none d-md-table-cell"><h6 class="fw-semibold mb-0">Aktivitas</h6></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query_log = "
                                SELECT la.*, p.nama
                                FROM log_aktivitas la
                                JOIN pengguna p ON la.pengguna_id = p.id
                                ORDER BY la.dibuat_pada DESC
                                LIMIT 5
                            ";
                            $result_log = mysqli_query($koneksi, $query_log);
                            if (mysqli_num_rows($result_log) > 0) {
                                while ($row = mysqli_fetch_assoc($result_log)):
                            ?>
                            <tr>
                                <td class="border-bottom-0"><h6 class="fw-normal mb-0 text-nowrap"><?= date('d M Y H:i', strtotime($row['dibuat_pada'])) ?></h6></td>
                                <td class="border-bottom-0">
                                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($row['nama']) ?></h6>
                                    <p class="mb-0 fw-normal d-md-none text-muted" style="font-size:12px;"><?= htmlspecialchars($row['aktivitas']) ?></p>
                                </td>
                                <td class="border-bottom-0 d-none d-md-table-cell">
                                    <p class="mb-0 fw-normal"><?= htmlspecialchars($row['aktivitas']) ?></p>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>Belum ada aktivitas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
