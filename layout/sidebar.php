<?php
// layout/sidebar.php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!-- Sidebar Start -->
<aside class="left-sidebar">
    <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
        <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2 mt-2">
            <img src="assets/images/logos/logo_tegal.png" alt="Logo Kabupaten Tegal" style="width: 70px; height: auto;">
            <h3 class="fw-bold text-primary mb-0">PENDEKAR</h3>
        </a>
        <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
        </div>
    </div>
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
        <ul id="sidebarnav">
        <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">Menu Utama</span>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link <?= ($current_page == 'dashboard') ? 'active' : '' ?>" href="index.php?page=dashboard" aria-expanded="false">
            <span><i class="ti ti-layout-dashboard"></i></span>
            <span class="hide-menu">Dashboard</span>
            </a>
        </li>

        <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff', 'struktural'])): ?>
        <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">Penyimpanan Dokumen</span>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link <?= ($current_page == 'dokumen') ? 'active' : '' ?>" href="index.php?page=dokumen" aria-expanded="false">
            <span><i class="ti ti-file-description"></i></span>
            <span class="hide-menu">Dokumen</span>
            </a>
        </li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <li class="sidebar-item">
            <a class="sidebar-link <?= ($current_page == 'kategori') ? 'active' : '' ?>" href="index.php?page=kategori" aria-expanded="false">
            <span><i class="ti ti-category"></i></span>
            <span class="hide-menu">Kategori</span>
            </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
        <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">Manajemen Pengguna</span>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link <?= ($current_page == 'pengguna') ? 'active' : '' ?>" href="index.php?page=pengguna" aria-expanded="false">
            <span><i class="ti ti-users"></i></span>
            <span class="hide-menu">Pengguna</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link <?= ($current_page == 'log_aktivitas') ? 'active' : '' ?>" href="index.php?page=log_aktivitas" aria-expanded="false">
            <span><i class="ti ti-history"></i></span>
            <span class="hide-menu">Log Aktivitas</span>
            </a>
        </li>
        <?php endif; ?>

        </ul>
    </nav>
    </div>
</aside>
<!--  Sidebar End -->
