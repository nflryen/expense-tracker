<?php
require_once '../config.php';
require_once '../crud/admin-crud.php';
require_once '../crud/user-crud.php';

requireAdmin();

$admin_id = $_SESSION['user_id'];
$admin = getUserById($admin_id);

$stats = getGlobalStats();
$recent_transactions = getRecentTransactionsGlobal(8);

$page_title = 'Admin Dashboard';

include '../includes/header.php';
include '../includes/admin-sidebar.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">
            <i class="bi bi-shield-check text-warning me-2"></i>
            Admin Dashboard
        </h1>
        <p class="text-muted mb-0">Overview sistem Dompet Kita</p>
    </div>
</div>

<!-- Statistik Utama -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Transaksi</h6>
                        <h3><?php echo number_format($stats['total_transactions']); ?></h3>
                    </div>
                    <i class="bi bi-list-ul fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">User Aktif (bulan ini)</h6>
                        <h3><?php echo number_format($stats['active_users']); ?></h3>
                    </div>
                    <i class="bi bi-person-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Volume</h6>
                        <h3><?php echo formatRupiah($stats['total_income'] + $stats['total_expense']); ?></h3>
                    </div>
                    <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Keuangan -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card income-card">
            <div class="card-body text-center">
                <h5>Total Pemasukan Global</h5>
                <h3>+ <?php echo formatRupiah($stats['total_income']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card expense-card">
            <div class="card-body text-center">
                <h5>Total Pengeluaran Global</h5>
                <h3>- <?php echo formatRupiah($stats['total_expense']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card balance-card">
            <div class="card-body text-center">
                <h5>Saldo Global</h5>
                <h3><?php echo formatRupiah($stats['total_income'] - $stats['total_expense']); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transaksi Terbaru -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Transaksi Terbaru (Global)</h5>
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_transactions)): ?>
                <div class="text-center p-4">
                    <i class="bi bi-cash-stack fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Belum ada transaksi</p>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recent_transactions as $transaction): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="transaction-icon me-3">
                                    <?php echo $transaction['category_icon']; ?>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($transaction['description']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo formatTanggal($transaction['date']); ?> • 
                                        <?php echo htmlspecialchars($transaction['category_name']); ?> •
                                        <strong><?php echo htmlspecialchars($transaction['username']); ?></strong>
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge <?php echo $transaction['type'] === 'income' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>
                                    <?php echo formatRupiah($transaction['amount']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
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
                        <span class="fw-bold text-primary"><?php echo number_format($stats['total_users']); ?></span>
                    </div>
                    <div class="ms-3">
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total Transaksi:</span>
                        <span class="fw-bold text-success"><?php echo number_format($stats['total_transactions']); ?></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>User Aktif:</span>
                        <span class="fw-bold text-info"><?php echo number_format($stats['active_users']); ?></span>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Volume Global:</span>
                        <span class="fw-bold text-warning">
                            <?php echo formatRupiah($stats['total_income'] + $stats['total_expense']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kategori Paling Populer -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kategori Paling Populer</h5>
            </div>
            <div class="card-body">
                <?php if (empty($stats['popular_categories'])): ?>
                <div class="text-center text-muted">
                    <i class="bi bi-pie-chart fs-1"></i>
                    <p class="mt-2">Belum ada data</p>
                </div>
                <?php else: ?>
                <?php foreach ($stats['popular_categories'] as $index => $category): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                    </div>
                    <span class="badge bg-light text-dark"><?php echo number_format($category['usage_count']); ?> kali</span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>