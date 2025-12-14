<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addForm" action="add-transaction.php" method="POST">
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
                            <option value="Makan" data-type="expense">🍔 Makan</option>
                            <option value="Jajan" data-type="expense">🍭 Jajan</option>
                            <option value="Transport" data-type="expense">🚗 Transport</option>
                            <option value="Token" data-type="expense">⚡ Token</option>
                            <option value="Laundry" data-type="expense">👕 Laundry</option>
                            <option value="Internet" data-type="expense">🌐 Internet</option>
                            <option value="Parkir" data-type="expense">🅿️ Parkir</option>
                            <option value="Pulsa" data-type="expense">📱 Pulsa</option>
                            <option value="Entertainment" data-type="expense">🎬 Entertainment</option>
                            <option value="Kesehatan" data-type="expense">💊 Kesehatan</option>
                            <option value="Pendidikan" data-type="expense">📚 Pendidikan</option>
                            <option value="Lainnya" data-type="expense">💰 Lainnya</option>
                            
                            <!-- Kategori Pemasukan -->
                            <option value="Gaji" data-type="income">💰 Gaji</option>
                            <option value="Uang Saku" data-type="income">💵 Uang Saku</option>
                            <option value="Bonus" data-type="income">🎁 Bonus</option>
                            <option value="Investasi" data-type="income">📈 Investasi</option>
                            <option value="Freelance" data-type="income">💻 Freelance</option>
                            <option value="Hadiah" data-type="income">🎉 Hadiah</option>
                            <option value="Lainnya" data-type="income">💰 Lainnya</option>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>