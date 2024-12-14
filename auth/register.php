<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE nim = ? OR email = ?");
            $stmt->execute([$nim, $email]);

            if ($stmt->rowCount() > 0) {
                $error = "NIM atau email sudah terdaftar!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (nim, nama, email, password, role) VALUES (?, ?, ?, ?, 'mahasiswa')");
                $stmt->execute([$nim, $nama, $email, $hashed_password]);

                $success = "Registrasi berhasil! Silakan login.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kantin Pintar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/auth-style.css" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <!-- Left Side - Illustration -->
        <div class="auth-illustration">
            <img src="/assets/img/auth-illustration.svg" alt="Register Illustration">
            <h2>Bergabung dengan Kantin Pintar</h2>
            <p>Nikmati kemudahan pesan makanan di kampus</p>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
            <div class="auth-header">
                <h1>Daftar Akun</h1>
                <p>Lengkapi data diri Anda untuk membuat akun</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nim">NIM</label>
                    <input type="text" id="nim" name="nim" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Daftar</button>
            </form>

            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>