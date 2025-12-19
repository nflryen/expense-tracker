<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Ambil parameter filter
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$type = $_GET['type'] ?? '';

// Ambil data statistik
$stats = getMonthlyStats($user_id, $month, $year);
$total_income = $stats['total_income'] ?? 0;
$total_expense = $stats['total_expense'] ?? 0;
$balance = $total_income - $total_expense;

// Ambil breakdown kategori
$category_breakdown = getCategoryBreakdown($user_id, $month, $year);

// Ambil transaksi untuk periode ini
$filters = ['month' => $month, 'year' => $year];
if ($type) $filters['type'] = $type;

$transactions_result = getTransactions($user_id, $filters, 1, 1000); // Ambil banyak untuk laporan
$transactions = $transactions_result['data'];

// Daftar bulan dan tahun
$months = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$years = [];
for ($i = 0; $i < 5; $i++) {
    $years[] = date('Y') - $i;
}

$page_title = 'Laporan Keuangan';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">Laporan Keuangan</h1>
        <p class="text-muted mb-0">Analisis pemasukan dan pengeluaran Anda</p>
    </div>
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak Laporan
    </button>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="month">
                    <?php foreach ($months as $key => $name): ?>
                    <option value="<?php echo $key; ?>" <?php echo $month === $key ? 'selected' : ''; ?>>
                        <?php echo $name; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="year">
                    <?php foreach ($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipe</label>
                <select class="form-select" name="type">
                    <option value="">Semua</option>
                    <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                    <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card income-card">
            <div class="card-body text-center">
                <h6>Total Pemasukan</h6>
                <h4>+ <?php echo formatRupiah($total_income); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card expense-card">
            <div class="card-body text-center">
                <h6>Total Pengeluaran</h6>
                <h4>- <?php echo formatRupiah($total_expense); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card balance-card">
            <div class="card-body text-center">
                <h6>Saldo</h6>
                <h4 class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <?php echo formatRupiah($balance); ?>
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <h6>Total Transaksi</h6>
                <h4><?php echo count($transactions); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Breakdown Kategori -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Breakdown per Kategori</h5>
            </div>
            <div class="card-body">
                <?php if (empty($category_breakdown)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-pie-chart fs-1"></i>
                    <p class="mt-2">Tidak ada data untuk periode ini</p>
                </div>
                <?php else: ?>
                <?php foreach ($category_breakdown as $category): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="d-flex align-items-center">
                            <span class="me-2"><?php echo $category['category_icon']; ?></span>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </span>
                        <span class="fw-bold"><?php echo formatRupiah($category['total']); ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" 
                             style="width: <?php echo min(100, ($category['total'] / max(1, $total_expense)) * 100); ?>%; 
                                    background-color: <?php echo $category['category_color']; ?>;">
                        </div>
                    </div>
                    <small class="text-muted">
                        <?php echo number_format(($category['total'] / max(1, $total_expense)) * 100, 1); ?>% dari total pengeluaran
                    </small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transaksi Terbesar -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaksi Terbesar</h5>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-cash-stack fs-1"></i>
                    <p class="mt-2">Tidak ada transaksi untuk periode ini</p>
                </div>
                <?php else: ?>
                <?php 
                // Urutkan transaksi berdasarkan jumlah terbesar
                usort($transactions, function($a, $b) {
                    return $b['amount'] - $a['amount'];
                });
                $top_transactions = array_slice($transactions, 0, 5);
                ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($top_transactions as $transaction): ?>
                    <div class="list-group-item px-0">
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
</div>

<!-- Detail Transaksi -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detail Transaksi - <?php echo $months[$month] . ' ' . $year; ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($transactions)): ?>
        <div class="text-center p-5">
            <i class="bi bi-cash-stack fs-1 text-muted"></i>
            <h5 class="mt-3">Tidak ada transaksi</h5>
            <p class="text-muted">Belum ada transaksi untuk periode yang dipilih</p>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo formatTanggal($transaction['date']); ?></td>
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

<?php
include '../includes/footer.php';
?>