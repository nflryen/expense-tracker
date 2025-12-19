<?php
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

function getGlobalStats() {
    $db = getDB();
    
    $stats = [];
    
    // Total semua users (admin + user)
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Breakdown users by role
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stats['total_admins'] = $result->fetch_assoc()['count'];
    
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['total_regular_users'] = $result->fetch_assoc()['count'];
    
    // Total transaksi
    $result = $db->query("SELECT COUNT(*) as count FROM transactions");
    $stats['total_transactions'] = $result->fetch_assoc()['count'];
    
    // Total income dan expense
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income'");
    $stats['total_income'] = $result->fetch_assoc()['total'];
    
    $result = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'expense'");
    $stats['total_expense'] = $result->fetch_assoc()['total'];
    
    // User aktif bulan ini (yang punya transaksi)
    $result = $db->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM transactions 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $stats['active_users'] = $result->fetch_assoc()['count'];
    
    // Kategori populer
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

function deleteUser($user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        return false;
    }
    
    if ($user_id == $_SESSION['user_id']) {
        return false;
    }
    
    if ($user['role'] === 'admin') {
        $stmt = $db->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->get_result()->fetch_assoc()['admin_count'];
        
        if ($admin_count <= 1) {
            return false;
        }
    }
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    return $stmt->execute();
}

// Update role user
function updateUserRole($user_id, $role) {
    $db = getDB();
    
    if (empty($user_id) || empty($role)) {
        return false;
    }
    
    if (!in_array($role, ['user', 'admin'])) {
        return false;
    }
    
    $check_stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        return false;
    }
    
    // Update role
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    return $stmt->execute();
}

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

// Ambil semua transaksi
function getAllTransactionsGlobal($filters = [], $page = 1, $per_page = 20) {
    $db = getDB();
    
    $where = "WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['search'])) {
        $where .= " AND (t.description LIKE ? OR t.notes LIKE ? OR u.username LIKE ?)";
        $search_param = '%' . $filters['search'] . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
    }
    
    if (!empty($filters['type'])) {
        $where .= " AND t.type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
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