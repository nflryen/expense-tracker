<?php
// Config will be included by the calling file

// ========== USER CRUD OPERATIONS ==========

// Registrasi user baru
function registerUser($username, $email, $password) {
    $db = getDB();
    
    // Cek apakah username sudah ada
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false; // Username atau email sudah ada
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);
    
    return $stmt->execute();
}

// Login user
function loginUser($username, $password) {
    $db = getDB();
    
    // Cari user berdasarkan username
    $stmt = $db->prepare("SELECT id, username, email, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Verifikasi password
        if (password_verify($password, $user['password_hash'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    
    return false;
}

// Update profil user
function updateUserProfile($user_id, $username, $email, $monthly_budget) {
    $db = getDB();
    
    // Cek apakah username/email sudah digunakan user lain
    $stmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false; // Username atau email sudah digunakan
    }
    
    $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, monthly_budget = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $username, $email, $monthly_budget, $user_id);
    
    return $stmt->execute();
}

// Ubah password user
function changeUserPassword($user_id, $current_password, $new_password) {
    $db = getDB();
    
    // Verifikasi password lama
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!password_verify($current_password, $result['password_hash'])) {
        return false; // Password lama tidak benar
    }
    
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $new_hash, $user_id);
    
    return $stmt->execute();
}

// Ambil data user
function getUserById($user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT username, email, monthly_budget, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Ambil statistik user
function getUserStats($user_id) {
    $db = getDB();
    
    $stats = [];
    
    // Total transaksi
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['total_transactions'] = $stmt->get_result()->fetch_assoc()['count'];
    
    // Statistik bulanan
    $stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions 
        WHERE user_id = ? AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $monthly = $stmt->get_result()->fetch_assoc();
    
    $stats['monthly_income'] = $monthly['total_income'] ?? 0;
    $stats['monthly_expense'] = $monthly['total_expense'] ?? 0;
    $stats['monthly_balance'] = $stats['monthly_income'] - $stats['monthly_expense'];
    
    return $stats;
}

// Helper functions
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>