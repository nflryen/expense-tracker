<?php

function registerUser($username, $email, $password, $name = '') {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false;
    }
    
    // Hash password dengan MD5
    $password_hash = md5($password);
    
    // Jika name kosong, gunakan username
    if (empty($name)) {
        $name = $username;
    }
    
    $stmt = $db->prepare("INSERT INTO users (username, name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $name, $email, $password_hash);
    
    return $stmt->execute();
}

// Login user
function loginUser($username, $password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id, username, name, email, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
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
    
    $stmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return false;
    }
    
    $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, monthly_budget = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $username, $email, $monthly_budget, $user_id);
    
    return $stmt->execute();
}

// Ubah password user
function changeUserPassword($user_id, $current_password, $new_password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (md5($current_password) !== $result['password']) {
        return false;
    }
    
    $new_hash = md5($new_password);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_hash, $user_id);
    
    return $stmt->execute();
}

function getUserById($user_id) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT username, name, email, monthly_budget, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Ambil statistik user
function getUserStats($user_id) {
    $db = getDB();
    
    $stats = [];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM transactions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats['total_transactions'] = $stmt->get_result()->fetch_assoc()['count'];
    
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

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>