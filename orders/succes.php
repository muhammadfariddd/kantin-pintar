<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['order_id'])) {
    header("Location: /menu/index.php");
    exit();
}

$orderId = $_GET['order_id'];

// Ambil detail pesanan
$stmt = $conn->prepare("
    SELECT o.*, u.nama as nama_pembeli 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header("Location: /menu/index.php");
    exit();
}

// Ambil detail item pesanan
$stmt = $conn->prepare("
    SELECT oi.*, m.nama as nama_menu 
    FROM order_items oi 
    JOIN menu m ON oi.menu_id = m.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-4 text-success mb-4">✓</h1>
                    <h2>Pesanan Berhasil!</h2>
                    <p class="lead">Nomor Pesanan: #<?= $orderId ?></p>
                    <hr>

                    <div class="text-start mb-4">
                        <h5>Detail Pesanan:</h5>
                        <p>Nama: <?= htmlspecialchars($order['nama_pembeli']) ?></p>
                        <p>Waktu Pengambilan: <?= date('d/m/Y H:i', strtotime($order['waktu_pengambilan'])) ?></p>
                        <?php if ($order['catatan']): ?>
                            <p>Catatan: <?= htmlspecialchars($order['catatan']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Jumlah</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                                        <td><?= $item['jumlah'] ?></td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        Silakan tunjukkan nomor pesanan saat mengambil pesanan Anda.
                    </div>

                    <div class="mt-4">
                        <a href="/menu/index.php" class="btn btn-primary">Kembali ke Menu</a>
                        <a href="#" class="btn btn-outline-secondary" onclick="window.print()">Cetak</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>