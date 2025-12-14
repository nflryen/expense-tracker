<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');        // MAMP default, ubah ke '' untuk XAMPP
define('DB_NAME', 'dompet_sesat');
define('DB_PORT', 8889);          // MAMP default, ubah ke 3306 untuk XAMPP

// Koneksi Database
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($db->connect_error) {
                throw new Exception("Koneksi gagal: " . $db->connect_error);
            }
            
            $db->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Error database: " . $e->getMessage());
        }
    }
    
    return $db;
}

$conn = getDB();

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        // Detect current directory level and redirect accordingly
        $current_dir = basename(dirname($_SERVER['PHP_SELF']));
        if ($current_dir === 'user' || $current_dir === 'admin') {
            header('Location: ../auth/login.php');
        } else {
            header('Location: auth/login.php');
        }
        exit();
    }
}

// Fungsi format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
        '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
        '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
    ];
    
    $tgl = date('d', strtotime($tanggal));
    $bln = $bulan[date('m', strtotime($tanggal))];
    $thn = date('Y', strtotime($tanggal));
    
    return "$tgl $bln $thn";
}
?>