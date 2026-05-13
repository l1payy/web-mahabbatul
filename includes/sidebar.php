<!-- Sidebar for Mahabbatul Ummi -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-img">
                <img src="assets/logo.png" alt="Logo" style="height: 40px; width: auto; border-radius: 4px;">
            </div>
            <div class="logo-text">
                <h4>Mahabbatul Ummi</h4>
                <p>SISTEM MANAJEMEN SEKOLAH</p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="index.php">
                    <i data-lucide="layout-grid"></i>
                    <span>Beranda</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'absensi.php') ? 'active' : ''; ?>">
                <a href="absensi.php">
                    <i data-lucide="users"></i>
                    <span>Absensi</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'hafalan.php') ? 'active' : ''; ?>">
                <a href="hafalan.php">
                    <i data-lucide="book-open"></i>
                    <span>Hafalan</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <ul>
            <li>
                <a href="profil.php">
                    <i data-lucide="user-circle"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i data-lucide="log-out"></i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
<main class="content-area">