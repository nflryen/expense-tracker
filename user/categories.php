<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/category-crud.php';

requireLogin();


$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '💰');
        $color = trim($_POST['color'] ?? '#6b7280');
        $type = $_POST['type'] ?? 'expense';
        
        if (empty($name)) {
            $_SESSION['error'] = 'Nama kategori tidak boleh kosong';
        } else {
            if (addCategory($user_id, $name, $icon, $color, $type)) {
                $_SESSION['success'] = 'Kategori berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Kategori dengan nama tersebut sudah ada';
            }
        }
    }
    
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '💰');
        $color = trim($_POST['color'] ?? '#6b7280');
        $type = $_POST['type'] ?? 'expense';
        
        if (empty($name)) {
            $_SESSION['error'] = 'Nama kategori tidak boleh kosong';
        } else {
            if (updateCategory($id, $user_id, $name, $icon, $color, $type)) {
                $_SESSION['success'] = 'Kategori berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui kategori atau nama sudah ada';
            }
        }
    }
    
    header('Location: categories.php');
    exit();
}

// Proses delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteCategory($id, $user_id)) {
        $_SESSION['success'] = 'Kategori berhasil dihapus';
    } else {
        $_SESSION['error'] = 'Gagal menghapus kategori. Kategori mungkin masih digunakan atau merupakan kategori default';
    }
    
    header('Location: categories.php');
    exit();
}

// Ambil data
$categories = getAllCategories($user_id);
$usage_stats = getCategoryUsage($user_id);

// Pisahkan berdasarkan tipe
$income_categories = array_filter($categories, fn($cat) => $cat['type'] === 'income');
$expense_categories = array_filter($categories, fn($cat) => $cat['type'] === 'expense');

$page_title = 'Kelola Kategori';

// Ngambil header dan sidebar
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Pesan -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">Kelola Kategori</h1>
        <p class="text-muted mb-0">Atur kategori pemasukan dan pengeluaran Anda</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-circle"></i> Tambah Kategori
    </button>
</div>

<!-- Categories Layout -->
<div class="row">
    <!-- Kategori Pemasukan -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-down-left text-success"></i> 
                    Kategori Pemasukan (<?php echo count($income_categories); ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($income_categories as $category): ?>
                    <div class="col-12 mb-3">
                        <div class="card category-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center">
                                        <span class="category-icon me-2" style="background-color: <?php echo $category['color']; ?>">
                                            <?php echo $category['icon']; ?>
                                        </span>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                            <?php if ($category['is_default']): ?>
                                            <small class="text-muted">Default</small>
                                            <?php else: ?>
                                            <small class="text-muted">Custom</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!$category['is_default']): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Kategori Pengeluaran -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-up-right text-danger"></i> 
                    Kategori Pengeluaran (<?php echo count($expense_categories); ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($expense_categories as $category): ?>
                    <div class="col-12 mb-3">
                        <div class="card category-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center">
                                        <span class="category-icon me-2" style="background-color: <?php echo $category['color']; ?>">
                                            <?php echo $category['icon']; ?>
                                        </span>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                            <?php if ($category['is_default']): ?>
                                            <small class="text-muted">Default</small>
                                            <?php else: ?>
                                            <small class="text-muted">Custom</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!$category['is_default']): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a></li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <!-- Tipe -->
                    <div class="mb-3">
                        <label class="form-label">Tipe Kategori</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="add_income" value="income">
                            <label class="btn btn-outline-success" for="add_income">
                                <i class="bi bi-arrow-down-left"></i> Pemasukan
                            </label>
                            <input type="radio" class="btn-check" name="type" id="add_expense" value="expense" checked>
                            <label class="btn btn-outline-danger" for="add_expense">
                                <i class="bi bi-arrow-up-right"></i> Pengeluaran
                            </label>
                        </div>
                    </div>

                    <!-- Nama -->
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Hobi, Olahraga, dll" required>
                    </div>

                    <!-- Icon -->
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="icon" id="add_icon" value="💰" maxlength="2">
                            <button type="button" class="btn btn-outline-secondary" onclick="showEmojiPicker('add_icon')">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                        </div>
                        <div class="form-text">Pilih emoji yang mewakili kategori</div>
                    </div>

                    <!-- Warna -->
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color" name="color" value="#6b7280">
                            <div class="color-presets">
                                <button type="button" class="color-preset" data-color="#4361ee" style="background-color: #4361ee;"></button>
                                <button type="button" class="color-preset" data-color="#f72585" style="background-color: #f72585;"></button>
                                <button type="button" class="color-preset" data-color="#4cc9f0" style="background-color: #4cc9f0;"></button>
                                <button type="button" class="color-preset" data-color="#7209b7" style="background-color: #7209b7;"></button>
                                <button type="button" class="color-preset" data-color="#fca311" style="background-color: #fca311;"></button>
                                <button type="button" class="color-preset" data-color="#2ec4b6" style="background-color: #2ec4b6;"></button>
                            </div>
                        </div>
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

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <!-- Tipe -->
                    <div class="mb-3">
                        <label class="form-label">Tipe Kategori</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="edit_income" value="income">
                            <label class="btn btn-outline-success" for="edit_income">
                                <i class="bi bi-arrow-down-left"></i> Pemasukan
                            </label>
                            <input type="radio" class="btn-check" name="type" id="edit_expense" value="expense">
                            <label class="btn btn-outline-danger" for="edit_expense">
                                <i class="bi bi-arrow-up-right"></i> Pengeluaran
                            </label>
                        </div>
                    </div>

                    <!-- Nama -->
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>

                    <!-- Icon -->
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="icon" id="edit_icon" maxlength="2">
                            <button type="button" class="btn btn-outline-secondary" onclick="showEmojiPicker('edit_icon')">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Warna -->
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color" name="color" id="edit_color">
                            <div class="color-presets">
                                <button type="button" class="color-preset" data-color="#4361ee" style="background-color: #4361ee;"></button>
                                <button type="button" class="color-preset" data-color="#f72585" style="background-color: #f72585;"></button>
                                <button type="button" class="color-preset" data-color="#4cc9f0" style="background-color: #4cc9f0;"></button>
                                <button type="button" class="color-preset" data-color="#7209b7" style="background-color: #7209b7;"></button>
                                <button type="button" class="color-preset" data-color="#fca311" style="background-color: #fca311;"></button>
                                <button type="button" class="color-preset" data-color="#2ec4b6" style="background-color: #2ec4b6;"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// custom scripts untuk halaman ini
$custom_scripts = '
    // Edit kategori
    function editCategory(category) {
        document.getElementById("edit_id").value = category.id;
        document.getElementById("edit_name").value = category.name;
        document.getElementById("edit_icon").value = category.icon;
        document.getElementById("edit_color").value = category.color;
        
        if (category.type === "income") {
            document.getElementById("edit_income").checked = true;
        } else {
            document.getElementById("edit_expense").checked = true;
        }
        
        new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
    }

    // Hapus kategori
    function deleteCategory(id, name) {
        if (confirm("Yakin ingin menghapus kategori \"" + name + "\"?\\nKategori yang masih digunakan tidak dapat dihapus.")) {
            window.location.href = "categories.php?delete=" + id;
        }
    }

    // Color presets
    document.querySelectorAll(".color-preset").forEach(btn => {
        btn.addEventListener("click", function() {
            const color = this.dataset.color;
            const modal = this.closest(".modal");
            const colorInput = modal.querySelector("input[type=color]");
            colorInput.value = color;
        });
    });

    // Emoji picker placeholder
    function showEmojiPicker(inputId) {
        const emojis = ["💰", "🍚", "🍔", "⚡", "👕", "🚗", "🌐", "🅿️", "📱", "🎬", "💊", "📚", "🏆", "📈", "🎁", "🏠", "✈️", "🛒", "⛽", "🎮"];
        const emoji = prompt("Pilih emoji atau ketik emoji yang diinginkan:", emojis.join(" "));
        if (emoji && emoji.trim()) {
            document.getElementById(inputId).value = emoji.trim().charAt(0);
        }
    }
';

include '../includes/footer.php';
?>