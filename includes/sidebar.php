<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <div class="sidebar bg-light vh-100 position-sticky top-0" id="sidebar">
                <div class="p-3">
                    <h6 class="text-muted text-uppercase fw-bold mb-3">Menu</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?> d-flex align-items-center" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'transactions' ? 'active' : ''; ?> d-flex align-items-center" href="transactions.php">
                                <i class="bi bi-list-ul me-2"></i>
                                Transaksi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-2"></i>
                                Tambah Transaksi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?> d-flex align-items-center" href="reports.php">
                                <i class="bi bi-bar-chart me-2"></i>
                                Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?> d-flex align-items-center" href="categories.php">
                                <i class="bi bi-tags me-2"></i>
                                Kategori
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="my-3">
                    
                    <h6 class="text-muted text-uppercase fw-bold mb-3">Akun</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?> d-flex align-items-center" href="profile.php">
                                <i class="bi bi-person me-2"></i>
                                Profil
                            </a>
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