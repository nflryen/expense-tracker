<?php
require_once '../config.php';
require_once '../crud/admin-crud.php';
require_once '../crud/user-crud.php';

requireAdmin();

if ((isset($_POST['action']) && $_POST['action'] === 'update_role') || isset($_POST['btnupdate'])) {
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    
    if (empty($user_id) || empty($role)) {
        echo "<div class='alert alert-danger'>Data tidak lengkap!</div>";
    } elseif (updateUserRole($user_id, $role)) {
        echo "<div class='alert alert-success'>Role user berhasil diperbarui!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui role user!</div>";
    }
}

if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    if (deleteUser($user_id)) {
        echo "<div class='alert alert-success'>User berhasil dihapus!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menghapus user!</div>";
    }
}

// Ambil parameter
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

if (isset($_GET['search'])) {
    $search = $_GET['search'];
} else {
    $search = '';
}

$result = getAllUsers($page, 15, $search);
$users = $result['data'];
$total_pages = $result['total_pages'];

$page_title = 'Kelola Users - Admin';

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

<?php if ($total_pages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
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
                    <button type="submit" name="btnupdate" class="btn btn-warning">
                        <i class="bi bi-check"></i> Ubah Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Peringatan Keras!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><strong>Yakin ingin menghapus user: <span id="deleteUsername"></span>?</strong></h6>
                </div>
                
                <p><strong>Yang akan terhapus:</strong></p>
                <ul class="text-danger">
                    <li>Akun user</li>
                    <li>Semua transaksi user</li>
                    <li>Semua kategori custom user</li>
                </ul>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Tindakan ini TIDAK DAPAT DIBATALKAN!</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i> Ya, Hapus User
                </button>
            </div>
        </div>
    </div>
</div>

<?php
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
    
    // Validasi form sebelum submit
    document.addEventListener("DOMContentLoaded", function() {
        const roleForm = document.querySelector("#roleModal form");
        if (roleForm) {
            roleForm.addEventListener("submit", function(e) {
                const userId = document.getElementById("role_user_id").value;
                const selectedRole = document.querySelector("input[name=role]:checked");
                
                if (!userId || !selectedRole) {
                    e.preventDefault();
                    alert("Harap pilih role yang valid!");
                    return false;
                }
                
                // Konfirmasi perubahan role
                const confirmation = confirm("Yakin ingin mengubah role user " + document.getElementById("role_username").textContent + " menjadi " + selectedRole.value + "?");
                if (!confirmation) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });

    // Hapus user
    function deleteUser(userId, username) {
        // Set data ke modal
        document.getElementById("deleteUsername").textContent = username;
        
        // Hapus event listener lama dan set yang baru
        const confirmBtn = document.getElementById("confirmDeleteBtn");
        
        // Clone node untuk menghapus semua event listener
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Set event listener baru
        newConfirmBtn.onclick = function() {
            window.location.href = "users.php?delete=" + userId;
        };
        
        // Tampilkan modal
        new bootstrap.Modal(document.getElementById("deleteUserModal")).show();
    }


';

include '../includes/footer.php';
?>