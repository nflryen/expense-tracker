<?php
// Tentukan halaman aktif berdasarkan nama file
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <div class="sidebar bg-light vh-100 position-sticky top-0" id="sidebar">
                <div class="p-3">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-shield-check text-warning me-2 fs-4"></i>
                        <h6 class="text-muted text-uppercase fw-bold mb-0">Admin Panel</h6>
                    </div>
                    
                    <h6 class="text-muted text-uppercase fw-bold mb-3">Dashboard</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?> d-flex align-items-center" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?> d-flex align-items-center" href="users.php">
                                <i class="bi bi-people me-2"></i>
                                Kelola Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'transactions' ? 'active' : ''; ?> d-flex align-items-center" href="transactions.php">
                                <i class="bi bi-list-ul me-2"></i>
                                Semua Transaksi
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="my-3">
                    
                    <h6 class="text-muted text-uppercase fw-bold mb-3">Akun</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="profile.php">
                                <i class="bi bi-person-gear me-2"></i>
                                Profil Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger d-flex align-items-center" href="../auth/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="p-4">