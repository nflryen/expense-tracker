<?php

// ========== ADMIN CRUD OPERATIONS ==========

// Require admin access
function requireAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.';
        header('Location: ../user/dashboard.php');
        exit();
    }
}

// Ambil semua users untuk admin
function getAllUsers($page = 1, $per_page = 20, $search = '') {
    $db = getDB();
    
    $where = "WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $where .= " AND (username LIKE ? OR email LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }
    
    // Hitung total
    $count_query = "SELECT COUNT(*) as total FROM users $where";
    if (!empty($params)) {
        $count_stmt = $db->prepare($count_query);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total = $db->query($count_query)->fetch_assoc()['total'];
    }
    
    // Ambil data dengan pagination
    $query = "
        SELECT id, username, email, role, monthly_budget, created_at,
               (SELECT COUNT(*) FROM transactions WHERE user_id = users.id) as transaction_count,
               (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = users.id AND type = 'income') as total_income,
               (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = users.id AND type = 'expense') as total_expense
        FROM users 
        $where 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $offset = ($page - 1) * $per_page;
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $db->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    
    $result = $stmt->get_result();
    $users = [];
    
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return [
        'data' => $users,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];
}

// Ambil statistik global untuk admin
function getGlobalStats() {
    $db = getDB();
    
    $stats = [];
    
    // Total users
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Total transaksi
    $result = $db->query("SELECT COUNT(*) as count FROM transactions");
    $stats['total_transactions'] = $result->fetch_assoc()['count'];
    
    // Total pemasukan global
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'");
    $stats['total_income'] = $result->fetch_assoc()['total'];
    
    // Total pengeluaran global
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'");
    $stats['total_expense'] = $result->fetch_assoc()['total'];
    
    // User aktif bulan ini (yang ada transaksi)
    $result = $db->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM transactions 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $stats['active_users'] = $result->fetch_assoc()['count'];
    
    // Kategori paling populer
    $result = $db->query("
        SELECT c.name, COUNT(*) as usage_count
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        GROUP BY c.id, c.name
        ORDER BY usage_count DESC
        LIMIT 5
    ");
    $stats['popular_categories'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $stats;
}

// Hapus user (admin only)
function deleteUser($user_id) {
    $db = getDB();
    
    // Pastikan user ada
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        return false; // User tidak ditemukan
    }
    
    // Tidak bisa hapus diri sendiri
    if ($user_id == $_SESSION['user_id']) {
        return false; // Tidak bisa hapus diri sendiri
    }
    
    // Cek apakah ini admin terakhir
    if ($user['role'] === 'admin') {
        $stmt = $db->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->get_result()->fetch_assoc()['admin_count'];
        
        if ($admin_count <= 1) {
            return false; // Tidak bisa hapus admin terakhir
        }
    }
    
    // Hapus user (transaksi dan kategori akan terhapus otomatis karena CASCADE)
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    return $stmt->execute();
}

// Update role user
function updateUserRole($user_id, $role) {
    $db = getDB();
    
    if (!in_array($role, ['user', 'admin'])) {
        return false;
    }
    
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    return $stmt->execute();
}

// Ambil transaksi terbaru global (admin)
function getRecentTransactionsGlobal($limit = 10) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               u.username
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC 
        LIMIT ?
    ");
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $transactions = [];
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

// Ambil semua transaksi global dengan filter (admin)
function getAllTransactionsGlobal($filters = [], $page = 1, $per_page = 20) {
    $db = getDB();
    
    $where = "WHERE 1=1";
    $params = [];
    $types = "";
    
    // Filter berdasarkan pencarian
    if (!empty($filters['search'])) {
        $where .= " AND (t.description LIKE ? OR t.notes LIKE ? OR u.username LIKE ?)";
        $search_param = '%' . $filters['search'] . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
    }
    
    // Filter berdasarkan tipe
    if (!empty($filters['type'])) {
        $where .= " AND t.type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    // Filter berdasarkan user
    if (!empty($filters['user'])) {
        $where .= " AND u.username LIKE ?";
        $user_param = '%' . $filters['user'] . '%';
        $params[] = $user_param;
        $types .= "s";
    }
    
    // Hitung total
    $count_query = "
        SELECT COUNT(*) as total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        JOIN users u ON t.user_id = u.id
        $where
    ";
    
    if (!empty($params)) {
        $count_stmt = $db->prepare($count_query);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total = $db->query($count_query)->fetch_assoc()['total'];
    }
    
    // Ambil data dengan pagination
    $query = "
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
               u.username, u.email
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        JOIN users u ON t.user_id = u.id
        $where 
        ORDER BY t.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $offset = ($page - 1) * $per_page;
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $db->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    
    $result = $stmt->get_result();
    $transactions = [];
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return [
        'data' => $transactions,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];
}

// Ambil user detail untuk admin
function getUserDetail($user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT u.*,
               (SELECT COUNT(*) FROM transactions WHERE user_id = u.id) as transaction_count,
               (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = u.id AND type = 'income') as total_income,
               (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = u.id AND type = 'expense') as total_expense,
               (SELECT COUNT(*) FROM categories WHERE user_id = u.id) as custom_categories
        FROM users u
        WHERE u.id = ?
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
?>