<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /auth/login.php");
    exit();
}

$success = '';
$error = '';

// Ambil data admin
try {
    // Cek apakah admin sudah ada di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();

    // Jika belum ada admin, buat data admin default
    if (!$admin) {
        $conn->beginTransaction();

        $default_admin = [
            'nim' => 'admin',
            'nama' => 'Administrator',
            'email' => 'admin@kantinpintar.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin'
        ];

        $stmt = $conn->prepare("INSERT INTO users (nim, nama, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $default_admin['nim'],
            $default_admin['nama'],
            $default_admin['email'],
            $default_admin['password'],
            $default_admin['role']
        ]);

        $admin = $default_admin;
        $admin['id'] = $conn->lastInsertId();
        $admin['created_at'] = date('Y-m-d H:i:s');

        $conn->commit();
    }
} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    $error = $e->getMessage();
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';

    try {
        $conn->beginTransaction();

        // Update nama dan email
        $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ? AND role = 'admin'");
        $stmt->execute([$nama, $email, $_SESSION['user_id']]);

        // Update password jika diisi
        if (!empty($password_baru)) {
            if (!password_verify($password_lama, $admin['password'])) {
                throw new Exception("Password lama tidak sesuai");
            }

            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        }

        $conn->commit();
        $success = "Profil berhasil diperbarui";

        // Update session
        $_SESSION['user_name'] = $nama;

        // Refresh data admin
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$_SESSION['user_id']]);
        $admin = $stmt->fetch();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

include '../admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Profil Admin</h1>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Profil</h6>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($admin['nim']) ?>" readonly>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="Administrator" readonly>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($admin['nama']) ?>" required>
                                <div class="invalid-feedback">Nama tidak boleh kosong</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                                <div class="invalid-feedback">Email tidak valid</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-4 text-primary">Ubah Password</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Password Lama</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password_lama">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password_baru">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt me-2"></i>Keamanan & Aktivitas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Aktivitas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($admin['created_at'])) ?></td>
                                    <td>Pendaftaran akun admin</td>
                                    <td><span class="badge bg-success">Berhasil</span></td>
                                </tr>
                                <tr>
                                    <td><?= date('d/m/Y H:i') ?></td>
                                    <td>Login terakhir</td>
                                    <td><span class="badge bg-primary">Aktif</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Form validation
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>

<?php include '../admin/includes/footer.php'; ?>