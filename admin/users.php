<?php
require_once '../config.php';
require_once '../crud/admin-crud.php';
require_once '../crud/user-crud.php';

requireAdmin();

// Proses actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_role') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';
        
        if (updateUserRole($user_id, $role)) {
            $_SESSION['success'] = 'Role user berhasil diperbarui';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui role user';
        }
    }
    
    header('Location: users.php');
    exit();
}

// Proses delete
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if (deleteUser($user_id)) {
        $_SESSION['success'] = 'User berhasil dihapus';
    } else {
        $_SESSION['error'] = 'Gagal menghapus user. Kemungkinan: user tidak ditemukan, mencoba hapus diri sendiri, atau mencoba hapus admin terakhir.';
    }
    
    header('Location: users.php');
    exit();
}

// Ambil parameter
$page = (int)($_GET['page'] ?? 1);
$search = $_GET['search'] ?? '';

// Ambil data users
$result = getAllUsers($page, 15, $search);
$users = $result['data'];
$total_pages = $result['total_pages'];

// Set page title
$page_title = 'Kelola Users - Admin';

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
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">
            <i class="bi bi-people text-primary me-2"></i>
            Kelola Users
        </h1>
        <p class="text-muted mb-0">Manajemen semua pengguna sistem</p>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Cari User</label>
                <input type="text" class="form-control" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Cari berdasarkan username atau email...">
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="users.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistik Users -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Users</h5>
                <h3><?php echo number_format($result['total']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-info text-white">
            <div class="card-body text-center">
                <h5>Admin</h5>
                <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body text-center">
                <h5>Regular Users</h5>
                <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'user')); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Users -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
        <div class="text-center p-5">
            <i class="bi bi-people fs-1 text-muted"></i>
            <h5 class="mt-3">Tidak ada user</h5>
            <p class="text-muted">Belum ada user yang sesuai dengan pencarian Anda</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Statistik</th>
                        <th>Bergabung</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                </div>
                                <div>
                                    <div class="fw-medium"><?php echo htmlspecialchars($user['username']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="small">
                                <div><strong><?php echo number_format($user['transaction_count']); ?></strong> transaksi</div>
                                <div class="text-success">+<?php echo formatRupiah($user['total_income']); ?></div>
                                <div class="text-danger">-<?php echo formatRupiah($user['total_expense']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo formatTanggal($user['created_at']); ?></div>
                            <small class="text-muted"><?php echo date('H:i', strtotime($user['created_at'])); ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" 
                                        onclick="viewUser(<?php echo $user['id']; ?>)" 
                                        title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <!-- Tidak bisa edit/hapus diri sendiri -->
                                <button class="btn btn-outline-warning" 
                                        onclick="changeRole(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo $user['role']; ?>')" 
                                        title="Ubah Role">
                                    <i class="bi bi-person-gear"></i>
                                </button>
                                <button class="btn btn-outline-danger" 
                                        onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
                                        title="Hapus User">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php else: ?>
                                <!-- Diri sendiri -->
                                <span class="badge bg-secondary">Anda</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <!-- Previous -->
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        
        <!-- Pages -->
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>
        
        <!-- Next -->
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal Ubah Role -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Role User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" id="role_user_id">
                <div class="modal-body">
                    <p>Ubah role untuk user: <strong id="role_username"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="role" id="role_user" value="user">
                            <label class="btn btn-outline-primary" for="role_user">
                                <i class="bi bi-person"></i> User
                            </label>
                            <input type="radio" class="btn-check" name="role" id="role_admin" value="admin">
                            <label class="btn btn-outline-danger" for="role_admin">
                                <i class="bi bi-shield-check"></i> Admin
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Admin memiliki akses penuh ke sistem termasuk mengelola user lain.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check"></i> Ubah Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail User -->
<div class="modal fade" id="userDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Set custom scripts untuk halaman ini
$custom_scripts = '
    // Ubah role user
    function changeRole(userId, username, currentRole) {
        document.getElementById("role_user_id").value = userId;
        document.getElementById("role_username").textContent = username;
        
        if (currentRole === "admin") {
            document.getElementById("role_admin").checked = true;
        } else {
            document.getElementById("role_user").checked = true;
        }
        
        new bootstrap.Modal(document.getElementById("roleModal")).show();
    }

    // Hapus user
    function deleteUser(userId, username) {
        const message = "PERINGATAN KERAS!\\n\\n" +
                       "Yakin ingin menghapus user: " + username + "?\\n\\n" +
                       "Yang akan terhapus:\\n" +
                       "- Akun user\\n" +
                       "- Semua transaksi user\\n" +
                       "- Semua kategori custom user\\n\\n" +
                       "Tindakan ini TIDAK DAPAT DIBATALKAN!\\n\\n" +
                       "Ketik HAPUS untuk konfirmasi:";
        
        const confirmation = prompt(message);
        if (confirmation === "HAPUS") {
            window.location.href = "users.php?delete=" + userId;
        } else if (confirmation !== null) {
            alert("Konfirmasi tidak sesuai. User tidak dihapus.");
        }
    }

    // Lihat detail user
    function viewUser(userId) {
        const modal = new bootstrap.Modal(document.getElementById("userDetailModal"));
        const content = document.getElementById("userDetailContent");
        
        // Show loading
        content.innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        modal.show();
        
        // Load user detail (simplified - in real app would use AJAX)
        setTimeout(() => {
            content.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Fitur detail user akan dikembangkan lebih lanjut.
                    Untuk saat ini, Anda dapat melihat statistik dasar di tabel.
                </div>
            `;
        }, 1000);
    }
';

include '../includes/footer.php';
?>