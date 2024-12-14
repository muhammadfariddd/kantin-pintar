<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Filter tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Awal bulan ini
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Hari ini

// Ambil laporan penjualan
$stmt = $conn->prepare("
    SELECT 
        DATE(o.created_at) as tanggal,
        COUNT(DISTINCT o.id) as total_pesanan,
        SUM(o.total_harga) as total_penjualan
    FROM orders o
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE(o.created_at)
    ORDER BY tanggal DESC
");
$stmt->execute([$start_date, $end_date]);
$reports = $stmt->fetchAll();

// Hitung total
$total_orders = 0;
$total_sales = 0;
foreach ($reports as $report) {
    $total_orders += $report['total_pesanan'];
    $total_sales += $report['total_penjualan'];
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h2>Laporan Penjualan</h2>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Pesanan</h5>
                    <h2><?= $total_orders ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Penjualan</h5>
                    <h2>Rp <?= number_format($total_sales, 0, ',', '.') ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Laporan -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Pesanan</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($report['tanggal'])) ?></td>
                                <td><?= $report['total_pesanan'] ?></td>
                                <td>Rp <?= number_format($report['total_penjualan'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>