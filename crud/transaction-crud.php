<?php
// Config will be included by the calling file

// ========== TRANSACTION CRUD OPERATIONS ==========

// Tambah transaksi baru
function addTransaction($user_id, $type, $description, $amount, $category, $date, $notes = '') {
    $db = getDB();
    
    // Get atau buat kategori
    $category_id = getOrCreateCategory($user_id, $category, $type);
    
    if (!$category_id) {
        return false;
    }
    
    $sql = "INSERT INTO transactions (user_id, category_id, type, description, amount, date, notes) VALUES ('$user_id', '$category_id', '$type', '$description', '$amount', '$date', '$notes')";
    $result = $db->query($sql);
    
    return $result;
}

// Update transaksi
function updateTransaction($id, $user_id, $type, $description, $amount, $category_id, $date, $notes = '') {
    $db = getDB();
    
    // Jika category_id adalah string (nama kategori), convert ke ID
    if (!is_numeric($category_id)) {
        $category_id = getOrCreateCategory($user_id, $category_id, $type);
        if (!$category_id) {
            return false;
        }
    }
    
    $sql = "UPDATE transactions SET category_id = '$category_id', type = '$type', description = '$description', amount = '$amount', date = '$date', notes = '$notes' WHERE id = '$id' AND user_id = '$user_id'";
    $result = $db->query($sql);
    
    return $result;
}

// Hapus transaksi
function deleteTransaction($id, $user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    return $stmt->execute();
}

// Ambil semua transaksi user
function getTransactions($user_id, $filters = [], $page = 1, $per_page = 20) {
    $db = getDB();
    
    $where = "WHERE t.user_id = ?";
    $params = [$user_id];
    $types = "i";
    
    // Filter berdasarkan pencarian
    if (!empty($filters['search'])) {
        $where .= " AND (t.description LIKE ? OR t.notes LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $types .= "ss";
    }
    
    // Filter berdasarkan tipe
    if (!empty($filters['type'])) {
        $where .= " AND t.type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    // Filter berdasarkan bulan
    if (!empty($filters['month'])) {
        $where .= " AND MONTH(t.date) = ?";
        $params[] = $filters['month'];
        $types .= "i";
    }
    
    // Filter berdasarkan tahun
    if (!empty($filters['year'])) {
        $where .= " AND YEAR(t.date) = ?";
        $params[] = $filters['year'];
        $types .= "i";
    }
    
    // Hitung total
    $count_query = "
        SELECT COUNT(*) as total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        $where
    ";
    
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    
    $query = "
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        $where 
        ORDER BY t.date DESC, t.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $offset = ($page - 1) * $per_page;
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param($types, ...$params);
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

// Ambil transaksi terbaru
function getRecentTransactions($user_id, $limit = 3) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.date DESC, t.created_at DESC 
        LIMIT ?
    ");
    
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $transactions = [];
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

// Ambil satu transaksi
function getTransaction($id, $user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT t.*, c.name as category_name 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.id = ? AND t.user_id = ?
    ");
    
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Ambil statistik bulanan
function getMonthlyStats($user_id, $month = null, $year = null) {
    $db = getDB();
    
    if (!$month) $month = date('m');
    if (!$year) $year = date('Y');
    
    $stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions 
        WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
    ");
    
    $stmt->bind_param("iii", $user_id, $month, $year);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Ambil breakdown kategori
function getCategoryBreakdown($user_id, $month = null, $year = null) {
    $db = getDB();
    
    if (!$month) $month = date('m');
    if (!$year) $year = date('Y');
    
    $stmt = $db->prepare("
        SELECT 
            c.name as category_name,
            c.icon as category_icon,
            c.color as category_color,
            SUM(t.amount) as total,
            COUNT(*) as count
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND MONTH(t.date) = ? AND YEAR(t.date) = ? AND t.type = 'expense'
        GROUP BY c.id, c.name, c.icon, c.color
        ORDER BY total DESC
    ");
    
    $stmt->bind_param("iii", $user_id, $month, $year);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $breakdown = [];
    
    while ($row = $result->fetch_assoc()) {
        $breakdown[] = $row;
    }
    
    return $breakdown;
}

// Helper function untuk kategori
function getOrCreateCategory($user_id, $category_name, $type) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? AND type = ? AND (user_id = ? OR is_default = TRUE) LIMIT 1");
    $stmt->bind_param("ssi", $category_name, $type, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }
    
    // Buat kategori baru jika tidak ada
    $icon = getCategoryIcon($category_name);
    $color = getCategoryColor($category_name);
    
    $stmt = $db->prepare("INSERT INTO categories (user_id, name, icon, color, type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $category_name, $icon, $color, $type);
    
    if ($stmt->execute()) {
        return $db->insert_id;
    }
    
    return false;
}

function getCategoryColor($category_name) {
    $colors = [
        'Makan' => '#4361ee',
        'Jajan' => '#f72585',
        'Token' => '#4cc9f0',
        'Laundry' => '#7209b7',
        'Transport' => '#fca311',
        'Internet' => '#2ec4b6',
        'Parkir' => '#e71d36',
        'Pulsa' => '#ff9f1c',
        'Entertainment' => '#9b5de5',
        'Kesehatan' => '#00bbf9',
        'Pendidikan' => '#f15bb5',
        'Gaji' => '#10b981',
        'Uang Saku' => '#3b82f6',
        'Bonus' => '#8b5cf6',
        'Investasi' => '#f59e0b'
    ];
    
    return $colors[$category_name] ?? '#6b7280';
}

function getCategoryIcon($category_name) {
    $icons = [
        'Makan' => '🍚',
        'Jajan' => '🍔',
        'Token' => '⚡',
        'Laundry' => '👕',
        'Transport' => '🚗',
        'Internet' => '🌐',
        'Parkir' => '🅿️',
        'Pulsa' => '📱',
        'Entertainment' => '🎬',
        'Kesehatan' => '💊',
        'Pendidikan' => '📚',
        'Gaji' => '💰',
        'Uang Saku' => '🎁',
        'Bonus' => '🏆',
        'Investasi' => '📈'
    ];
    
    return $icons[$category_name] ?? '💰';
}
?>