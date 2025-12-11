<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';

requireLogin();

// Redirect admin ke dashboard admin
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$transaction_id = (int)($_GET['id'] ?? 0);

// Ambil data transaksi
$transaction = getTransaction($transaction_id, $user_id);

if (!$transaction) {
    $_SESSION['error'] = 'Transaksi tidak ditemukan';
    header('Location: transactions.php');
    exit();
}

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'expense';
    $description = trim($_POST['description'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validasi
    if (empty($description) || $amount <= 0 || empty($category)) {
        $error = 'Semua field wajib harus diisi';
    } else {
        // Update transaksi
        if (updateTransaction($transaction_id, $user_id, $type, $description, $amount, $category, $date, $notes)) {
            $_SESSION['success'] = 'Transaksi berhasil diperbarui!';
            header('Location: transactions.php');
            exit();
        } else {
            $error = 'Gagal memperbarui transaksi';
        }
    }
}

// Set page title
$page_title = 'Edit Transaksi';

// Include header dan sidebar
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-pencil"></i> Edit Transaksi
                </h5>
                <a href="transactions.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Tipe Transaksi -->
                    <div class="mb-3">
                        <label class="form-label">Tipe Transaksi</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="income" value="income" 
                                   <?php echo $transaction['type'] === 'income' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-success" for="income">
                                <i class="bi bi-arrow-down-left"></i> Pemasukan
                            </label>
                            <input type="radio" class="btn-check" name="type" id="expense" value="expense"
                                   <?php echo $transaction['type'] === 'expense' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-danger" for="expense">
                                <i class="bi bi-arrow-up-right"></i> Pengeluaran
                            </label>
                        </div>
                    </div>

                    <!-- Jumlah -->
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="amount" 
                                   value="<?php echo $transaction['amount']; ?>" 
                                   min="100" required>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" name="description" 
                               value="<?php echo htmlspecialchars($transaction['description']); ?>" 
                               placeholder="Untuk apa?" required>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category" id="category" required>
                            <option value="">Pilih kategori</option>
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date" 
                               value="<?php echo $transaction['date']; ?>" required>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea class="form-control" name="notes" rows="2" 
                                  placeholder="Catatan tambahan..."><?php echo htmlspecialchars($transaction['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-check"></i> Simpan Perubahan
                        </button>
                        <a href="transactions.php" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Set custom scripts untuk halaman ini
$custom_scripts = '
    // Load kategori saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        const currentType = "' . $transaction['type'] . '";
        const currentCategory = "' . htmlspecialchars($transaction['category_name']) . '";
        
        filterCategories(currentType);
        
        // Set kategori setelah kategori difilter
        setTimeout(() => {
            document.getElementById("category").value = currentCategory;
        }, 100);
        
        // Event listener untuk perubahan tipe
        document.querySelectorAll("input[name=\'type\']").forEach(radio => {
            radio.addEventListener("change", function() {
                filterCategories(this.value);
            });
        });
    });
';

include '../includes/footer.php';
?>