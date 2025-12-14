<?php
require_once '../config.php';
require_once '../crud/user-crud.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

// Proses registrasi
if (isset($_POST['btnregister'])) {
    require_once "../config.php";
    
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($username) || empty($name) || empty($email) || empty($password)) {
        echo "<div class='alert alert-danger'>Semua field harus diisi!</div>";
    } elseif (strlen($username) < 3) {
        echo "<div class='alert alert-danger'>Username minimal 3 karakter!</div>";
    } elseif (strlen($password) < 6) {
        echo "<div class='alert alert-danger'>Password minimal 6 karakter!</div>";
    } elseif ($password != $confirm_password) {
        echo "<div class='alert alert-danger'>Konfirmasi password tidak cocok!</div>";
    } else {
        // Cek apakah username atau email sudah ada
        $check_sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $result = $conn->query($check_sql);
        
        if ($result->num_rows > 0) {
            echo "<div class='alert alert-danger'>Username atau email sudah digunakan!</div>";
        } else {
            // Insert user baru
            $insert_sql = "INSERT INTO users (username, name, email, password, role) VALUES ('$username', '$name', '$email', MD5('$password'), 'user')";
            
            if ($conn->query($insert_sql)) {
                echo "<div class='alert alert-success'>Registrasi berhasil! <a href='login.php'>Silakan login</a></div>";
            } else {
                echo "<div class='alert alert-danger'>Terjadi kesalahan saat registrasi!</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Dompet Sesat</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-wallet2"></i> Dompet Sesat
                        </h3>
                        <small class="text-muted">Pencatat Keuangan Anak Kost</small>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="text-center mb-4">Daftar Akun Baru</h5>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                       placeholder="Minimal 3 karakter" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       placeholder="Nama lengkap Anda" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       placeholder="email@example.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" 
                                       placeholder="Minimal 6 karakter" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" name="confirm_password" 
                                       placeholder="Ulangi password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="btnregister" class="btn btn-primary btn-lg">
                                    <i class="bi bi-person-plus"></i> Daftar
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <div class="text-center">
                            <p class="text-muted">Sudah punya akun?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Masuk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>