<?php
error_reporting(0);
session_start();

if (isset($_SESSION['user']) && isset($_SESSION['level'])) {
    if ($_SESSION['level'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

// Proses login
if (isset($_POST['btnlogin'])) {
    require_once "../config.php";
    
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    
    $sql = "SELECT * FROM users WHERE username = ? AND password = MD5(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $hasil = $stmt->get_result();
    $ada = $hasil->num_rows;
    
    if ($ada > 0) {
        $data = $hasil->fetch_array();
        $_SESSION['user'] = $user;
        $_SESSION['username'] = $user;
        $_SESSION['level'] = $data['role'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['name'] = $data['name'];
        
        if ($_SESSION['level'] == "admin") {
            header("Location: ../admin/dashboard.php");
        } elseif ($_SESSION['level'] == "user") {
            header("Location: ../user/dashboard.php");
        }
        exit();
    } else {
        $error_message = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dompet Sesat
    </title>
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
                        <small class="text-white">Pencatat Keuangan Anak Kost</small>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="text-center mb-4">Masuk ke Akun</h5>
                        
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form action="#" method="post">
                            <div class="input-group mb-3">
                                <div class="form-floating">
                                    <input id="loginUser" type="text" name="user" class="form-control" 
                                           value="" placeholder="" required/>
                                    <label for="loginUser">Username</label>
                                </div>
                                <div class="input-group-text">
                                    <span class="bi bi-person"></span>
                                </div>
                            </div>
                            
                            <div class="input-group mb-3">
                                <div class="form-floating">
                                    <input id="loginPassword" type="password" name="pass" class="form-control" 
                                           placeholder="" required />
                                    <label for="loginPassword">Password</label>
                                </div>
                                <div class="input-group-text">
                                    <span class="bi bi-lock-fill"></span>
                                </div>
                            </div>
                            
                            <!--begin::Row-->
                            <div class="row">
                                <div class="col-8 d-inline-flex align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" />
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Ingat Saya
                                        </label>
                                    </div>
                                </div>
                                <!-- /.col -->
                                <div class="col-4">
                                    <div class="d-grid gap-2">
                                        <input type="submit" class="btn btn-primary" value="Masuk" name="btnlogin" />
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <hr>

                        <div class="text-center">
                            <p class="text-muted">Belum punya akun?</p>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Daftar Baru
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