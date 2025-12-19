<?php
require_once '../config.php';
require_once '../crud/admin-crud.php';
require_once '../crud/user-crud.php';

requireAdmin();

$page = (int)($_GET['page'] ?? 1);
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$user_filter = $_GET['user'] ?? '';

$filters = [];
if (!empty($search)) $filters['search'] = $search;
if (!empty($type)) $filters['type'] = $type;

$db = getDB();

$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $where .= " AND (t.description LIKE ? OR t.notes LIKE ? OR u.username LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($type)) {
    $where .= " AND t.type = ?";
    $params[] = $type;
    $types .= "s";
}

if (!empty($user_filter)) {
    $where .= " AND u.username LIKE ?";
    $user_param = '%' . $user_filter . '%';
    $params[] = $user_param;
    $types .= "s";
}

// Hitung total
$count_query = "
    SELECT COUNT(*) as total 
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    JOIN users u ON t.user_id = u.id
    $where
";

if (!empty($params)) {
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $db->query($count_query)->fetch_assoc()['total'];
}

$per_page = 20;
$query = "
    SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
           u.username, u.email
    FROM transactions t
    JOIN categories c ON t.category_id = c.id
    JOIN users u ON t.user_id = u.id
    $where 
    ORDER BY t.created_at DESC 
    LIMIT ? OFFSET ?
";

$offset = ($page - 1) * $per_page;
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();

$result = $stmt->get_result();
$transactions = [];

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

$total_pages = ceil($total / $per_page);

// Statistik
$stats_query = "
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
        COUNT(DISTINCT user_id) as unique_users
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    $where
";

if (!empty($params)) {
    $stats_params = array_slice($params, 0, -2);
    $stats_types = substr($types, 0, -2);
    
    if (!empty($stats_params)) {
        $stats_stmt = $db->prepare($stats_query);
        $stats_stmt->bind_param($stats_types, ...$stats_params);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
    } else {
        $stats = $db->query($stats_query)->fetch_assoc();
    }
} else {
    $stats = $db->query($stats_query)->fetch_assoc();
}

$page_title = 'Semua Transaksi - Admin';

include '../includes/header.php';
include '../includes/admin-sidebar.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">
            <i class="bi bi-list-ul text-success me-2"></i>
            Semua Transaksi (Global)
        </h1>
        <p class="text-muted mb-0">Monitor semua transaksi pengguna</p>
    </div>
</div>

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Transaksi</h5>
                <h3><?php echo number_format($stats['total_transactions']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card income-card">
            <div class="card-body text-center">
                <h5>Total Pemasukan</h5>
                <h3>+ <?php echo formatRupiah($stats['total_income']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card expense-card">
            <div class="card-body text-center">
                <h5>Total Pengeluaran</h5>
                <h3>- <?php echo formatRupiah($stats['total_expense']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body text-center">
                <h5>Users Terlibat</h5>
                <h3><?php echo number_format($stats['unique_users']); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cari</label>
                <input type="text" class="form-control" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Cari transaksi atau user...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipe</label>
                <select class="form-select" name="type">
                    <option value="">Semua</option>
                    <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                    <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <input type="text" class="form-control" name="user" 
                       value="<?php echo htmlspecialchars($user_filter); ?>" 
                       placeholder="Filter berdasarkan username...">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="transactions.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Transaksi -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($transactions)): ?>
        <div class="text-center p-5">
            <i class="bi bi-cash-stack fs-1 text-muted"></i>
            <h5 class="mt-3">Tidak ada transaksi</h5>
            <p class="text-muted">Belum ada transaksi yang sesuai dengan filter Anda</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>User</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td>
                            <div><?php echo formatTanggal($transaction['date']); ?></div>
                            <small class="text-muted"><?php echo date('H:i', strtotime($transaction['created_at'])); ?></small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-2" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                    <?php echo strtoupper(substr($transaction['username'], 0, 2)); ?>
                                </div>
                                <div>
                                    <div class="fw-medium"><?php echo htmlspecialchars($transaction['username']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($transaction['email']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium"><?php echo htmlspecialchars($transaction['description']); ?></div>
                            <?php if (!empty($transaction['notes'])): ?>
                            <small class="text-muted"><?php echo htmlspecialchars($transaction['notes']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                <?php echo $transaction['category_icon']; ?> 
                                <?php echo htmlspecialchars($transaction['category_name']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $transaction['type'] === 'income' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $transaction['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran'; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold <?php echo $transaction['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>
                                <?php echo formatRupiah($transaction['amount']); ?>
                            </span>
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&user=<?php echo urlencode($user_filter); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        
        <!-- Pages -->
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&user=<?php echo urlencode($user_filter); ?>">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>
        
        <!-- Next -->
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&user=<?php echo urlencode($user_filter); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php
include '../includes/footer.php';
?>