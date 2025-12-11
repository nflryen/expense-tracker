<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';
require_once '../crud/category-crud.php';

requireLogin();

// Redirect admin ke dashboard admin
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Proses form tambah transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'expense';
    $description = trim($_POST['description'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validasi
    if (empty($description) || $amount <= 0 || empty($category)) {
        $_SESSION['error'] = 'Semua field wajib harus diisi';
    } else {
        // Tambah transaksi
        if (addTransaction($user_id, $type, $description, $amount, $category, $date, $notes)) {
            $_SESSION['success'] = 'Transaksi berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan transaksi';
        }
    }
} else {
    $_SESSION['error'] = 'Method tidak valid';
}

// Redirect kembali ke halaman sebelumnya atau dashboard
$redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
header('Location: ' . $redirect);
exit();
?>