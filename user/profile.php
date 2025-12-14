<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Ambil data user
$db = getDB();
$stmt = $db->prepare("SELECT username, email, monthly_budget, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Proses form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $monthly_budget = (float)($_POST['monthly_budget'] ?? 0);
        
        if (empty($username) || empty($email)) {
            $_SESSION['error'] = 'Username dan email tidak boleh kosong';
        } else {
            // Cek apakah username/email sudah digunakan user lain
            $stmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['error'] = 'Username atau email sudah digunakan';
            } else {
                $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, monthly_budget = ? WHERE id = ?");
                $stmt->bind_param("ssdi", $username, $email, $monthly_budget, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['username'] = $username; // Update session
                    $_SESSION['success'] = 'Profil berhasil diperbarui';
                    
                    // Refresh data user
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['monthly_budget'] = $monthly_budget;
                } else {
                    $_SESSION['error'] = 'Gagal memperbarui profil';
                }
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
            // Verifikasi password lama
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!password_verify($current_password, $result['password_hash'])) {
                $_SESSION['error'] = 'Password lama tidak benar';
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Password berhasil diubah';
                } else {
                    $_SESSION['error'] = 'Gagal mengubah password';
                }
            }
        }
    }
    
    header('Location: profile.php');
    exit();
}

// Ambil statistik user
$stats = getMonthlyStats($user_id);
$total_transactions = 0;

$stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_transactions = $result['count'];

$page_title = 'Profil Saya';

// Ngambil header dan sidebar
include '../includes/header.php';
include '../includes/sidebar.php';
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
    <h1 class="h3">Profil Saya</h1>
    <p class="text-muted mb-0">Kelola informasi akun dan pengaturan Anda</p>
</div>

<div class="row">
    <!-- Informasi Profil -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Budget Bulanan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="monthly_budget" 
                                   value="<?php echo $user['monthly_budget']; ?>" min="0" step="1000">
                        </div>
                        <div class="form-text">Target budget pengeluaran per bulan</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Ubah Password -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ubah Password</h5>
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
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="confirm_password" 
                               minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-lock"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistik Akun -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Statistik Akun</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Bergabung sejak:</span>
                        <span class="fw-bold"><?php echo formatTanggal($user['created_at']); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Transaksi:</span>
                        <span class="fw-bold"><?php echo number_format($total_transactions); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Pemasukan Bulan Ini:</span>
                        <span class="fw-bold text-success">
                            <?php echo formatRupiah($stats['total_income'] ?? 0); ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Pengeluaran Bulan Ini:</span>
                        <span class="fw-bold text-danger">
                            <?php echo formatRupiah($stats['total_expense'] ?? 0); ?>
                        </span>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Saldo Saat Ini:</span>
                        <span class="fw-bold <?php echo ($stats['total_income'] - $stats['total_expense']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatRupiah(($stats['total_income'] ?? 0) - ($stats['total_expense'] ?? 0)); ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($user['monthly_budget'] > 0): ?>
                <div class="mb-3">
                    <label class="form-label mb-1">Progress Budget Bulanan</label>
                    <?php 
                    $budget_used = ($stats['total_expense'] ?? 0) / $user['monthly_budget'] * 100;
                    $budget_color = $budget_used > 100 ? 'bg-danger' : ($budget_used > 80 ? 'bg-warning' : 'bg-success');
                    ?>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar <?php echo $budget_color; ?>" 
                             style="width: <?php echo min(100, $budget_used); ?>%"></div>
                    </div>
                    <small class="text-muted">
                        <?php echo number_format($budget_used, 1); ?>% dari budget 
                        (<?php echo formatRupiah($user['monthly_budget']); ?>)
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Ngambil modal dan footer
include '../includes/modals/add-transaction-modal.php';
include '../includes/footer.php';
?>