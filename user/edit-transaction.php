<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';
require_once '../crud/category-crud.php';

requireLogin();

$user_id = $_SESSION['user_id'];
if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
} else {
    $transaction_id = 0;
}

// Ambil data transaksi
$transaction = getTransaction($transaction_id, $user_id);

if (!$transaction) {
    header('Location: transactions.php');
    exit();
}

// Ambil semua kategori untuk dropdown
$categories = getAllCategories($user_id);

// Proses form edit
if (isset($_POST['btnedit'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];
    
    if (!empty($description) && $amount > 0 && !empty($category)) {
        updateTransaction($transaction_id, $user_id, $type, $description, $amount, $category, $date, $notes);
        header('Location: transactions.php');
        exit();
    }
}

$page_title = 'Edit Transaksi';

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
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    data-type="<?php echo $cat['type']; ?>"
                                    <?php echo $cat['id'] == $transaction['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['icon'] . ' ' . htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
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
                                  placeholder="Catatan tambahan..."><?php 
                                  if (isset($transaction['notes'])) {
                                      echo htmlspecialchars($transaction['notes']);
                                  }
                                  ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="btnedit" class="btn btn-primary flex-fill">
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
$custom_scripts = '
    // Load kategori saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        const currentType = "' . $transaction['type'] . '";
        const currentCategory = "' . $transaction['category_id'] . '";
        
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