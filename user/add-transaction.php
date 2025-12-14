<?php
require_once '../config.php';
require_once '../crud/user-crud.php';
require_once '../crud/transaction-crud.php';
require_once '../crud/category-crud.php';

requireLogin();

if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Proses form tambah transaksi
if (isset($_POST['btnadd'])) {
    $type = $_POST['type'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];
    
    // Validasi
    if (empty($description) || $amount <= 0 || empty($category)) {
        echo "<div class='alert alert-danger'>Semua field wajib harus diisi!</div>";
    } else {
        // Tambah transaksi
        if (addTransaction($user_id, $type, $description, $amount, $category, $date, $notes)) {
            echo "<div class='alert alert-success'>Transaksi berhasil ditambahkan!</div>";
            header('Location: dashboard.php');
        } else {
            echo "<div class='alert alert-danger'>Gagal menambahkan transaksi!</div>";
        }
    }
}
?>