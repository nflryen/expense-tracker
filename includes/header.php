<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?> - Dompet kita</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-outline-light mobile-menu-btn me-2" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-wallet2"></i> Dompet Kita
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 d-none d-md-inline">
                    Halo, <?php echo htmlspecialchars($username); ?>!
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <span class="badge bg-warning text-dark ms-1">Admin</span>
                    <?php endif; ?>
                </span>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <!-- Menu khusus admin -->
                        <li><a class="dropdown-item" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard Admin
                        </a></li>
                        <li><a class="dropdown-item" href="users.php">
                            <i class="bi bi-people"></i> Kelola Users
                        </a></li>
                        <li><a class="dropdown-item" href="transactions.php">
                            <i class="bi bi-list-ul"></i> Monitor Transaksi
                        </a></li>
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person-gear"></i> Profil Admin
                        </a></li>
                        <?php else: ?>
                        <!-- Menu khusus user -->
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person-gear"></i> Profil
                        </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>