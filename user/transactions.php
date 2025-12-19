<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';
require_once '../crud/category-crud.php';

requireLogin();

if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$filters = [];
$page = 1;
$search = '';
$type = '';
$month = '';
$year = '';

if (isset($_GET['page'])) {
    $page = $_GET['page'];
}

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $filters['search'] = $search;
}

if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $filters['type'] = $type;
}

if (isset($_GET['month'])) {
    $month = $_GET['month'];
    $filters['month'] = $month;
}

if (isset($_GET['year'])) {
    $year = $_GET['year'];
    $filters['year'] = $year;
}

$result = getTransactions($user_id, $filters, $page, 15);
$transactions = $result['data'];
$total_pages = $result['total_pages'];

// Hapus transaksi jika diminta
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    deleteTransaction($delete_id, $user_id);
    header('Location: transactions.php');
    exit();
}

// Hitung total untuk statistik
if ($month && $year) {
    $stats = getMonthlyStats($user_id, $month, $year);
} else {
    $stats = getMonthlyStats($user_id);
}
if (isset($stats['total_income'])) {
    $total_income = $stats['total_income'];
} else {
    $total_income = 0;
}

if (isset($stats['total_expense'])) {
    $total_expense = $stats['total_expense'];
} else {
    $total_expense = 0;
}

// Daftar bulan dan tahun untuk filter
$months = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$years = [];
for ($i = 0; $i < 5; $i++) {
    $years[] = date('Y') - $i;
}

function buildPageUrl($page, $params) {
    $params['page'] = $page;
    return 'transactions.php?' . http_build_query($params);
}

$page_title = 'Semua Transaksi';

include '../includes/header.php';
include '../includes/sidebar.php';
?>



<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">Semua Transaksi</h1>
        <p class="text-muted mb-0">Kelola semua pemasukan dan pengeluaran Anda</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> Tambah Baru
    </button>
</div>

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card income-card">
            <div class="card-body text-center">
                <h5>Total Pemasukan</h5>
                <h3>+ <?php echo formatRupiah($total_income); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card expense-card">
            <div class="card-body text-center">
                <h5>Total Pengeluaran</h5>
                <h3>- <?php echo formatRupiah($total_expense); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card balance-card">
            <div class="card-body text-center">
                <h5>Saldo</h5>
                <h3><?php echo formatRupiah($total_income - $total_expense); ?></h3>
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
                       placeholder="Cari transaksi...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipe</label>
                <select class="form-select" name="type">
                    <option value="">Semua</option>
                    <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                    <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="month">
                    <option value="">Semua</option>
                    <?php foreach ($months as $key => $name): ?>
                    <option value="<?php echo $key; ?>" <?php echo $month === $key ? 'selected' : ''; ?>>
                        <?php echo $name; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="year">
                    <option value="">Semua</option>
                    <?php foreach ($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
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
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Transaksi</button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Aksi</th>
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
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="edit-transaction.php?id=<?php echo $transaction['id']; ?>" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" 
                                        onclick="confirmDelete(<?php echo $transaction['id']; ?>)" 
                                        title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
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
        <!-- Previous -->
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo buildPageUrl($page - 1, $_GET); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        
        <!-- Pages -->
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
            <a class="page-link" href="<?php echo buildPageUrl($i, $_GET); ?>">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>
        
        <!-- Next -->
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo buildPageUrl($page + 1, $_GET); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php
// Ambil kategori untuk modal
$expense_categories = getAllCategories($user_id, 'expense');
$income_categories = getAllCategories($user_id, 'income');

// Ngambil modal dan footer
include '../includes/modals/add-transaction-modal.php';

// custom scripts untuk halaman ini
$custom_scripts = '
    function confirmDelete(id) {
        if (confirm("Yakin ingin menghapus transaksi ini?\\nTindakan ini tidak dapat dibatalkan.")) {
            const params = new URLSearchParams(window.location.search);
            params.set("delete", id);
            window.location.href = "transactions.php?" + params.toString();
        }
    }
';

include '../includes/footer.php';
?>