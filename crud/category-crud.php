<?php
// Ambil semua kategori user
function getAllCategories($user_id, $type = null) {
    $db = getDB();
    
    $where = "WHERE (user_id = ? OR is_default = TRUE) AND name IS NOT NULL AND name != ''";
    $params = [$user_id];
    $types = "i";
    
    if ($type) {
        $where .= " AND type = ?";
        $params[] = $type;
        $types .= "s";
    }
    
    $stmt = $db->prepare("SELECT id, name, icon, color, type, is_default, user_id FROM categories $where ORDER BY is_default DESC, name");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Ambil kategori berdasarkan ID
function getCategoryById($id, $user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ? AND (user_id = ? OR is_default = TRUE)");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Tambah kategori baru
function addCategory($user_id, $name, $icon, $color, $type) {
    $db = getDB();
    
    // Cek apakah nama kategori sudah ada untuk user ini
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? AND (user_id = ? OR is_default = TRUE) AND type = ?");
    $stmt->bind_param("sis", $name, $user_id, $type);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false; // Kategori sudah ada
    }
    
    $stmt = $db->prepare("INSERT INTO categories (user_id, name, icon, color, type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $icon, $color, $type);
    
    return $stmt->execute();
}

// Update kategori
function updateCategory($id, $user_id, $name, $icon, $color, $type) {
    $db = getDB();
    
    // Pastikan kategori milik user dan bukan default
    $stmt = $db->prepare("SELECT is_default FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result || $result['is_default']) {
        return false;
    }
    
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? AND (user_id = ? OR is_default = TRUE) AND type = ? AND id != ?");
    $stmt->bind_param("sisi", $name, $user_id, $type, $id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false; // Nama kategori sudah ada
    }
    
    $stmt = $db->prepare("UPDATE categories SET name = ?, icon = ?, color = ?, type = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $name, $icon, $color, $type, $id, $user_id);
    
    return $stmt->execute();
}

// Hapus kategori
function deleteCategory($id, $user_id) {
    $db = getDB();
    
    // Pastikan kategori milik user dan bukan default
    $stmt = $db->prepare("SELECT is_default FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result || $result['is_default']) {
        return false; // Tidak bisa hapus kategori default atau milik orang lain
    }
    
    // Cek apakah kategori masih digunakan dalam transaksi
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        return false; // Kategori masih digunakan
    }
    
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    return $stmt->execute();
}

// Hitung penggunaan kategori
function getCategoryUsage($user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT c.id, c.name, c.type, COUNT(t.id) as usage_count, COALESCE(SUM(t.amount), 0) as total_amount
        FROM categories c
        LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ?
        WHERE (c.user_id = ? OR c.is_default = TRUE)
        GROUP BY c.id, c.name, c.type
        ORDER BY usage_count DESC, c.name
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Ambil kategori untuk dropdown
function getCategories($user_id, $type = null) {
    $db = getDB();
    
    $where = "WHERE (user_id = ? OR is_default = TRUE)";
    $params = [$user_id];
    $types = "i";
    
    if ($type) {
        $where .= " AND type = ?";
        $params[] = $type;
        $types .= "s";
    }
    
    $stmt = $db->prepare("SELECT name FROM categories $where ORDER BY name");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
    
    return $categories;
}
?>