<?php
// pages/log_aktivitas.php

// Hanya admin yang bisa melihat semua log aktivitas
cek_role('admin');

?>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Log Aktivitas Sistem</h5>
                </div>

                <div class="list-filter-toolbar mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" id="logSearch" placeholder="Cari aktivitas pengguna...">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label">Jenis Aktivitas</label>
                            <select class="form-select" id="logFilterJenis">
                                <option value="">Semua Jenis</option>
                                <option value="login">Login / Logout</option>
                                <option value="dokumen">Dokumen</option>
                                <option value="pengguna">Pengguna</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label">Role</label>
                            <select class="form-select" id="logFilterRole">
                                <option value="">Semua Role</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="struktural">Struktural</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label">Tanggal Aktivitas</label>
                            <input type="date" class="form-control" id="logFilterTanggal">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <button type="button" class="btn btn-outline-secondary w-100" id="logResetFilter">
                                <i class="ti ti-refresh"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                    <div class="list-filter-meta mt-2">
                        <span class="list-filter-count" id="logCount"></span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 align-middle table-bordered table-striped" id="tableLog">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th class="d-none d-md-table-cell">Role</th>
                                <th>Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "
                                SELECT la.*, p.nama, p.role 
                                FROM log_aktivitas la 
                                JOIN pengguna p ON la.pengguna_id = p.id 
                                ORDER BY la.dibuat_pada DESC
                            ");
                            
                            if (mysqli_num_rows($query) > 0) {
                                while($row = mysqli_fetch_assoc($query)):
                                    $jenis = jenis_aktivitas_log($row['aktivitas']);
                                    $search_blob = strtolower(
                                        $row['nama'] . ' ' . $row['role'] . ' ' . $row['aktivitas']
                                    );
                            ?>
                            <tr class="list-data-row"
                                data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES) ?>"
                                data-jenis="<?= htmlspecialchars($jenis, ENT_QUOTES) ?>"
                                data-role="<?= htmlspecialchars($row['role'], ENT_QUOTES) ?>"
                                data-date="<?= date('Y-m-d', strtotime($row['dibuat_pada'])) ?>">
                                <td><?= $no++ ?></td>
                                <td class="text-nowrap"><?= date('d M Y H:i:s', strtotime($row['dibuat_pada'])) ?></td>
                                <td>
                                    <h6 class="fw-semibold mb-0"><?= htmlspecialchars($row['nama']) ?></h6>
                                    <div class="d-md-none mt-1">
                                        <?php 
                                            if($row['role'] == 'admin') echo '<span class="badge bg-danger" style="font-size:10px;">Admin</span>';
                                            elseif($row['role'] == 'staff') echo '<span class="badge bg-primary" style="font-size:10px;">Staff</span>';
                                            else echo '<span class="badge bg-info" style="font-size:10px;">Struktural</span>';
                                        ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php 
                                        if($row['role'] == 'admin') echo '<span class="badge bg-danger rounded-3 fw-semibold">Admin</span>';
                                        elseif($row['role'] == 'staff') echo '<span class="badge bg-primary rounded-3 fw-semibold">Staff</span>';
                                        else echo '<span class="badge bg-info rounded-3 fw-semibold">Struktural</span>';
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['aktivitas']) ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="list-pagination-wrap" id="logPagination"></div>

                <script>
                window.addEventListener('load', function() {
                    if (document.getElementById('tableLog')) {
                        new PendekarListFilter({
                            table: '#tableLog',
                            searchInput: '#logSearch',
                            resetBtn: '#logResetFilter',
                            countEl: '#logCount',
                            paginationEl: '#logPagination',
                            perPage: 15,
                            emptyColspan: 5,
                            emptyMessage: 'Data tidak ditemukan',
                            filters: [
                                { el: '#logFilterJenis', key: 'jenis' },
                                { el: '#logFilterRole', key: 'role' },
                                { el: '#logFilterTanggal', key: 'date', match: 'date' }
                            ]
                        });
                    }
                });
                </script>

            </div>
        </div>
    </div>
</div>
