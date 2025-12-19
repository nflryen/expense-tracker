<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';
require_once '../crud/category-crud.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$db = getDB();
$stmt = $db->prepare("SELECT monthly_budget FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$monthly_budget = $user_data['monthly_budget'] ?? 0;

$stats = getMonthlyStats($user_id);
$recent_transactions = getRecentTransactions($user_id, 7);
$category_breakdown = getCategoryBreakdown($user_id);

$total_income = $stats['total_income'];
$total_expense = $stats['total_expense'];
$balance = $total_income - $total_expense;

$budget_used_percentage = 0;
$budget_remaining = 0;
$budget_status = 'safe';
$budget_alert = '';

if ($monthly_budget > 0) {
    $budget_used_percentage = ($total_expense / $monthly_budget) * 100;
    $budget_remaining = $monthly_budget - $total_expense;
    
    if ($budget_used_percentage >= 100) {
        $budget_status = 'danger';
        $budget_alert = 'Peringatan! Anda telah melebihi budget bulanan sebesar ' . formatRupiah(abs($budget_remaining)) . '.';
    } elseif ($budget_used_percentage >= 80) {
        $budget_status = 'warning';
        $budget_alert = 'Hati-hati! Anda sudah menggunakan ' . number_format($budget_used_percentage, 1) . '% dari budget bulanan.';
    } elseif ($budget_used_percentage >= 65) {
        $budget_status = 'info';
        $budget_alert = 'Sisa budget bulanan Anda: ' . formatRupiah($budget_remaining) . ' (' . number_format(100 - $budget_used_percentage, 1) . '%).';
    }
}

$page_title = 'Dashboard';

include '../includes/header.php';
include '../includes/sidebar.php';
?>


<!-- Statistik Utama -->
<div class="row mb-3">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card balance-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white">Saldo Saat Ini</h6>
                        <h3><?php echo formatRupiah($balance); ?></h3>
                    </div>
                    <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card income-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white">Pemasukan</h6>
                        <h3>+ <?php echo formatRupiah($total_income); ?></h3>
                    </div>
                    <i class="bi bi-arrow-down-left fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card stat-card expense-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white">Pengeluaran</h6>
                        <h3>- <?php echo formatRupiah($total_expense); ?></h3>
                    </div>
                    <i class="bi bi-arrow-up-right fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Tambah Cepat</h5>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-primary w-100 quick-btn" 
                                onclick="quickAdd('Makan', 20000)">
                            🍚 Makan
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-primary w-100 quick-btn" 
                                onclick="quickAdd('Jajan', 15000)">
                            🍔 Jajan
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-primary w-100 quick-btn" 
                                onclick="quickAdd('Transport', 10000)">
                            🚗 Transport
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-primary w-100 h-100" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">7 Transaksi Terbaru</h5>
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_transactions)): ?>
                <div class="text-center p-4">
                    <i class="bi bi-cash-stack fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Belum ada transaksi</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        Tambah Transaksi Pertama
                    </button>
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
                                        <?php echo htmlspecialchars($transaction['category_name']); ?>
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

    <!-- Kolom Kanan -->
    <div class="col-lg-4">
        <?php if ($monthly_budget > 0): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Budget Bulanan</h5>
                <a href="profile.php" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-gear"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="text-muted mb-1">Budget</h6>
                            <p class="mb-0 fw-bold"><?php echo formatRupiah($monthly_budget); ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Terpakai</h6>
                        <p class="mb-0 fw-bold text-<?php echo $budget_status === 'danger' ? 'danger' : ($budget_status === 'warning' ? 'warning' : 'success'); ?>">
                            <?php echo formatRupiah($total_expense); ?>
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progress</small>
                        <small class="text-muted"><?php echo number_format($budget_used_percentage, 1); ?>%</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-<?php echo $budget_status === 'danger' ? 'danger' : ($budget_status === 'warning' ? 'warning' : 'success'); ?>" 
                             style="width: <?php echo min(100, $budget_used_percentage); ?>%"></div>
                    </div>
                </div>
                
                <div class="mt-3"> 
                    <?php if ($budget_status === 'danger'): ?>
                        <div class="alert alert-danger py-2">
                            <small><i class="bi bi-exclamation-triangle"></i> 
                            <strong>Over Budget!</strong><br>
                            Pertimbangkan untuk mengurangi pengeluaran atau meninjau budget Anda.</small>
                        </div>
                    <?php elseif ($budget_status === 'warning'): ?>
                        <div class="alert alert-warning py-2">
                            <small><i class="bi bi-exclamation-circle"></i> 
                            <strong>Mendekati Limit!</strong><br>
                            Sisa budget: <?php echo formatRupiah($budget_remaining); ?>. Hati-hati dengan pengeluaran.</small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success py-2">
                            <small><i class="bi bi-check-circle"></i> 
                            <strong>Budget Aman!</strong><br>
                            Sisa budget: <?php echo formatRupiah($budget_remaining); ?>. Tetap bijak berbelanja!</small>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    $days_remaining = date('t') - date('j');
                    $daily_budget = $days_remaining > 0 ? $budget_remaining / $days_remaining : 0;
                    ?>
                    <?php if ($days_remaining > 0 && $budget_remaining > 0): ?>
                    <div class="text-center mt-2 p-2 bg-light rounded">
                        <small class="text-muted">Saran pengeluaran harian:</small><br>
                        <strong class="text-primary"><?php echo formatRupiah($daily_budget); ?>/hari</strong>
                        <small class="text-muted">(<?php echo $days_remaining; ?> hari tersisa)</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kategori Pengeluaran</h5>
            </div>
            <div class="card-body">
                <?php if (empty($category_breakdown)): ?>
                <div class="text-center text-muted">
                    <i class="bi bi-pie-chart fs-1"></i>
                    <p class="mt-2">Belum ada data pengeluaran</p>
                </div>
                <?php else: ?>
                <?php foreach ($category_breakdown as $category): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><?php echo htmlspecialchars($category['category_name']); ?></span>
                        <span class="fw-bold"><?php echo formatRupiah($category['total']); ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" 
                             style="width: <?php echo min(100, ($category['total'] / max(1, $total_expense)) * 100); ?>%; 
                                    background-color: <?php echo $category['category_color']; ?>;">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Ambil kategori untuk modal
$expense_categories = getAllCategories($user_id, 'expense');
$income_categories = getAllCategories($user_id, 'income');

include '../includes/modals/add-transaction-modal.php';

$custom_scripts = '';

include '../includes/footer.php';
?>