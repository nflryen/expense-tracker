<div class="modal fade" id="addModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeBtn"></button>
            </div>
            <form id="addForm" action="add-transaction.php" method="POST">
                <input type="hidden" name="redirect_to" id="redirect_to" value="">
                <div class="modal-body">
                    <!-- Tipe Transaksi -->
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="income" value="income">
                            <label class="btn btn-outline-success" for="income">
                                <i class="bi bi-arrow-down-left"></i> Pemasukan
                            </label>
                            <input type="radio" class="btn-check" name="type" id="expense" value="expense" checked>
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
                            <input type="number" class="form-control" name="amount" id="amount" 
                                   placeholder="0" min="100" required>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" name="description" id="description" 
                               placeholder="Untuk apa?" required>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category" id="category" required>
                            <option value="">Pilih kategori</option>
                            
                            <!-- Kategori Pengeluaran -->
                            <?php if (isset($expense_categories)): ?>
                                <?php foreach ($expense_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>" data-type="expense">
                                    <?php echo $category['icon']; ?> <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Kategori Pemasukan -->
                            <?php if (isset($income_categories)): ?>
                                <?php foreach ($income_categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>" data-type="income">
                                    <?php echo $category['icon']; ?> <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea class="form-control" name="notes" rows="2" 
                                  placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Batal</button>
                    <button type="submit" name="btnadd" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>