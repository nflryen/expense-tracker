<?php
require_once 'config.php';
require_once 'crud/user-crud.php';

// Redirect berdasarkan status login dan role
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
} else {
    header('Location: auth/login.php');
}
exit();
?>