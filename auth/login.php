<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: /admin/index.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'mahasiswa') {
        header("Location: /index.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Khusus untuk admin
    if ($role === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$nim]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = 'Administrator';
            $_SESSION['is_admin'] = true;

            header("Location: /admin/index.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        // Untuk user biasa
        $stmt = $conn->prepare("SELECT * FROM users WHERE nim = ? AND role = ?");
        $stmt->execute([$nim, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['is_admin'] = false;

            header("Location: /index.php");
            exit();
        } else {
            $error = "NIM atau password salah!";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kantin Pintar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/auth-style.css" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <!-- Left Side - Illustration -->
        <div class="auth-illustration">
            <img src="/assets/img/auth-illustration.svg" alt="Login Illustration" height="300px">
            <h2>Selamat Datang di Kantin Pintar</h2>
            <p>Pesan makanan dengan mudah dan cepat</p>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
            <div class="auth-header">
                <h1>Welcome Back!</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nim">NIM</label>
                    <input type="text" id="nim" name="nim" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="role">Login Sebagai</label>
                    <div class="role-select-container">
                        <select id="role" name="role" class="form-select" required>
                            <option value="" disabled selected>Pilih Role</option>
                            <option value="mahasiswa">
                                <i class="fas fa-user-graduate"></i> Mahasiswa
                            </option>
                            <option value="admin">
                                <i class="fas fa-user-shield"></i> Admin
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="auth-footer">
                <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>