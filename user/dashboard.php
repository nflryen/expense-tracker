<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';
require_once '../crud/category-crud.php';

requireLogin();

// Redirect admin ke dashboard admin
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil data statistik
$stats = getMonthlyStats($user_id);
$recent_transactions = getRecentTransactions($user_id, 3);
$category_breakdown = getCategoryBreakdown($user_id);

// Hitung balance
$total_income = $stats['total_income'] ?? 0;
$total_expense = $stats['total_expense'] ?? 0;
$balance = $total_income - $total_expense;

// Data untuk chart (sederhana)
$chart_labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
$chart_data = [];

// Buat data chart sederhana berdasarkan balance saat ini
for ($i = 0; $i < 7; $i++) {
    $variation = rand(-50000, 100000);
    $chart_data[] = max(0, $balance + $variation);
}
$chart_data[6] = $balance; // Hari terakhir = balance sekarang

// Set page title dan additional CSS/JS
$page_title = 'Dashboard';
$additional_css = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Include header dan sidebar
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Statistik Utama -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
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
    <div class="col-md-4 mb-3">
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
    <div class="col-md-4 mb-3">
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
        <!-- Tombol Quick Add -->
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

        <!-- Transaksi Terbaru (Dipindah ke atas) -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">3 Transaksi Terbaru</h5>
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

        <!-- Chart Balance (Dipindah ke bawah) -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Grafik Saldo Minggu Ini</h5>
                <canvas id="balanceChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-lg-4">
        <!-- Breakdown Kategori -->
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
// Include modal dan footer
include '../includes/modals/add-transaction-modal.php';

// Set custom scripts untuk halaman ini
$custom_scripts = '
    // Data chart dari PHP
    const chartLabels = ' . json_encode($chart_labels) . ';
    const chartData = ' . json_encode($chart_data) . ';

    // Inisialisasi chart
    const ctx = document.getElementById("balanceChart").getContext("2d");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: chartLabels,
            datasets: [{
                label: "Saldo",
                data: chartData,
                borderColor: "#4361ee",
                backgroundColor: "rgba(67, 97, 238, 0.1)",
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return "Rp " + value.toLocaleString("id-ID");
                        }
                    }
                }
            }
        }
    });

    // Quick add function
    function quickAdd(category, amount) {
        document.getElementById("expense").checked = true;
        document.getElementById("amount").value = amount;
        document.getElementById("description").value = category;
        filterCategories("expense");
        setTimeout(() => {
            document.getElementById("category").value = category;
        }, 100);
        
        new bootstrap.Modal(document.getElementById("addModal")).show();
    }
';

include '../includes/footer.php';
?>