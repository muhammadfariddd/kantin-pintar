<?php
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /auth/login.php");
    exit();
}

require_once '../config/database.php';

// Mengambil statistik
try {
    // Total Pendapatan Hari Ini
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total_harga), 0) as total 
        FROM orders 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $pendapatan_hari_ini = $stmt->fetchColumn();

    // Total Pesanan Hari Ini
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $pesanan_hari_ini = $stmt->fetchColumn();

    // Pesanan Menunggu
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $pesanan_pending = $stmt->fetchColumn();

    // Menu Terlaris
    $stmt = $conn->prepare("
        SELECT m.nama, COUNT(oi.menu_id) as total_pesanan
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.id
        GROUP BY oi.menu_id
        ORDER BY total_pesanan DESC
        LIMIT 5
    ");
    $stmt->execute();
    $menu_terlaris = $stmt->fetchAll();

    // Stok Menipis
    $stmt = $conn->prepare("
        SELECT nama, stok 
        FROM menu 
        WHERE stok <= 5 AND stok > 0
        ORDER BY stok ASC
    ");
    $stmt->execute();
    $stok_menipis = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Dashboard</h2>

    <!-- Statistik Utama -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pendapatan Hari Ini</h6>
                            <h3 class="mt-2 mb-0">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pesanan Hari Ini</h6>
                            <h3 class="mt-2 mb-0"><?= $pesanan_hari_ini ?></h3>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pesanan Menunggu</h6>
                            <h3 class="mt-2 mb-0"><?= $pesanan_pending ?></h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Terlaris & Stok Menipis -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Menu Terlaris</h5>
                </div>
                <div class="card-body">
                    <?php if ($menu_terlaris): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($menu_terlaris as $menu): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($menu['nama']) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $menu['total_pesanan'] ?> pesanan</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Belum ada data pesanan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Monitoring Stok</h5>
                    <?php if (count($stok_menipis) > 0): ?>
                        <span class="badge bg-danger">Perlu Perhatian</span>
                    <?php else: ?>
                        <span class="badge bg-success">Stok Aman</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($stok_menipis): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($stok_menipis as $menu): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($menu['nama']) ?></span>
                                    <span class="badge bg-danger rounded-pill">Stok: <?= $menu['stok'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                            <p class="text-muted mb-0">Semua menu memiliki stok yang mencukupi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>