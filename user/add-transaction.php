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

if (isset($_POST['btnadd'])) {
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $amount = isset($_POST['amount']) ? $_POST['amount'] : 0;
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // ini yg di ubah Revisi
    $redirect_to = 'dashboard.php';
    
    if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
        $redirect_to = $_POST['redirect_to'];
    } elseif (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        $referer_file = basename(parse_url($referer, PHP_URL_PATH));
        
        $valid_pages = ['dashboard.php', 'transactions.php', 'profile.php', 'reports.php'];
        
        if (in_array($referer_file, $valid_pages)) {
            $redirect_to = $referer_file;
        }
    }
    
    // Validasi dan tambah transaksi
    if (!empty($description) && $amount > 0 && !empty($category) && !empty($type)) {
        $result = addTransaction($user_id, $type, $description, $amount, $category, $date, $notes);
        
        if ($result) {
            header('Location: ' . $redirect_to . '?success=1');
            exit();
        } else {
            header('Location: ' . $redirect_to . '?error=database');
            exit();
        }
    } else {
        header('Location: ' . $redirect_to . '?error=validation');
        exit();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>