<?php
// layout/topbar.php
$user_nama = $_SESSION['user_nama'] ?? 'Pengguna';
$user_role = $_SESSION['user_role'] ?? '';
$role_label = label_role_pengguna($user_role);
$role_badge = badge_class_role($user_role);
$inisial = inisial_nama($user_nama);
?>
<!--  Main wrapper -->
<div class="body-wrapper">
    <!--  Header Start -->
    <header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-light">
        <ul class="navbar-nav">
        <li class="nav-item d-block d-xl-none">
            <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
            <i class="ti ti-menu-2"></i>
            </a>
        </li>
        </ul>
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
            <li class="nav-item d-none d-sm-flex align-items-center me-2">
                <span class="profile-greeting">Halo, <strong><?= htmlspecialchars($user_nama) ?></strong></span>
            </li>
            <li class="nav-item dropdown profile-nav">
            <a class="nav-link profile-trigger d-flex align-items-center"
               href="javascript:void(0)"
               id="profileDropdownTrigger"
               role="button"
               data-bs-toggle="dropdown"
               data-bs-auto-close="true"
               aria-expanded="false"
               aria-label="Menu profil">
                <div class="profile-avatar" title="<?= htmlspecialchars($user_nama) ?>">
                    <span class="profile-initials"><?= htmlspecialchars($inisial) ?></span>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end profile-dropdown-menu"
                 id="profileDropdownMenu"
                 aria-labelledby="profileDropdownTrigger">
                <div class="profile-dropdown-header">
                    <div class="profile-avatar">
                        <span class="profile-initials"><?= htmlspecialchars($inisial) ?></span>
                    </div>
                    <div class="profile-dropdown-info">
                        <h6><?= htmlspecialchars($user_nama) ?></h6>
                        <span class="profile-role-badge badge <?= $role_badge ?>"><?= htmlspecialchars($role_label) ?></span>
                    </div>
                </div>
                <div class="profile-dropdown-body">
                    <button type="button" class="profile-dropdown-item" id="btnGantiPassword">
                        <i class="ti ti-lock"></i>
                        <span>Ganti Password</span>
                    </button>
                    <div class="profile-dropdown-divider"></div>
                    <a href="logout.php" class="profile-dropdown-item logout-item">
                        <i class="ti ti-logout"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            </li>
        </ul>
        </div>
    </nav>
    </header>
    <!--  Header End -->

    <!-- Modal Ganti Password -->
    <div class="modal fade" id="modalGantiPassword" tabindex="-1" aria-labelledby="modalGantiPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGantiPasswordLabel">
                        <i class="ti ti-lock me-2"></i>Ganti Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formGantiPassword" autocomplete="off">
                    <div class="modal-body">
                        <div id="profilePasswordAlert" class="alert d-none" role="alert"></div>
                        <p class="text-muted small mb-3"><?= PENDEKAR_PASSWORD_HINT ?></p>

                        <div class="mb-3">
                            <label for="pwdLama" class="form-label">Password Lama</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-key"></i></span>
                                <input type="password" class="form-control" id="pwdLama" name="password_lama" required autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="pwdLama" tabindex="-1" aria-label="Tampilkan password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="pwdBaru" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="password" class="form-control pendekar-password-input" id="pwdBaru" name="password_baru" required minlength="8" autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="pwdBaru" tabindex="-1" aria-label="Tampilkan password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label for="pwdKonfirmasi" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-lock-check"></i></span>
                                <input type="password" class="form-control pendekar-password-input" id="pwdKonfirmasi" name="konfirmasi_password" required minlength="8" autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-target="pwdKonfirmasi" tabindex="-1" aria-label="Tampilkan password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanPassword">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container-fluid">
    <?php tampilkan_pendekar_flash(); ?>
