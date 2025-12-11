<?php
require_once '../config.php';
require_once '../crud/admin-crud.php';
require_once '../crud/user-crud.php';

requireAdmin();

$user_id = $_SESSION['user_id'];

// Ambil data admin
$admin = getUserById($user_id);

// Proses form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($username) || empty($email)) {
            $_SESSION['error'] = 'Username dan email tidak boleh kosong';
        } else {
            if (updateUserProfile($user_id, $username, $email, 0)) { // Admin tidak butuh monthly budget
                $_SESSION['username'] = $username; // Update session
                $_SESSION['success'] = 'Profil admin berhasil diperbarui';
                
                // Refresh data admin
                $admin['username'] = $username;
                $admin['email'] = $email;
            } else {
                $_SESSION['error'] = 'Username atau email sudah digunakan';
            }
        }
    }
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Semua field password harus diisi';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
        } else {
            if (changeUserPassword($user_id, $current_password, $new_password)) {
                $_SESSION['success'] = 'Password berhasil diubah';
            } else {
                $_SESSION['error'] = 'Password lama tidak benar';
            }
        }
    }
    
    header('Location: profile.php');
    exit();
}

// Ambil statistik admin
$global_stats = getGlobalStats();

// Set page title
$page_title = 'Profil Admin';

// Include header dan admin sidebar
include '../includes/header.php';
include '../includes/admin-sidebar.php';
?>

<!-- Pesan -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Header -->
<div class="mb-4">
    <h1 class="h3">
        <i class="bi bi-shield-check text-warning me-2"></i>
        Profil Admin
    </h1>
    <p class="text-muted mb-0">Kelola informasi akun administrator</p>
</div>

<div class="row">
    <!-- Informasi Profil Admin -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge"></i> Informasi Admin
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username Admin</label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Admin</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Ubah Password -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-shield-lock"></i> Keamanan Admin
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="new_password" 
                               minlength="6" required>
                        <div class="form-text">Minimal 6 karakter untuk keamanan</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="confirm_password" 
                               minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-shield-lock"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistik Admin -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i> Statistik Sistem
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Admin sejak:</span>
                        <span class="fw-bold"><?php echo formatTanggal($admin['created_at']); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Users:</span>
                        <span class="fw-bold text-primary"><?php echo number_format($global_stats['total_users']); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Transaksi:</span>
                        <span class="fw-bold text-success"><?php echo number_format($global_stats['total_transactions']); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>User Aktif:</span>
                        <span class="fw-bold text-info"><?php echo number_format($global_stats['active_users']); ?></span>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Volume Global:</span>
                        <span class="fw-bold text-warning">
                            <?php echo formatRupiah($global_stats['total_income'] + $global_stats['total_expense']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-tools"></i> Admin Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard Admin
                    </a>
                    <a href="users.php" class="btn btn-outline-info">
                        <i class="bi bi-people"></i> Kelola Users
                    </a>
                    <a href="transactions.php" class="btn btn-outline-success">
                        <i class="bi bi-list-ul"></i> Monitor Transaksi
                    </a>
                    <hr>
                    <a href="../auth/logout.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>