<?php
session_start();
require_once '../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tambahkan fungsi debug
function debug_to_file($message)
{
    error_log(date('[Y-m-d H:i:s] ') . print_r($message, true) . "\n", 3, '../logs/checkout.log');
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Ambil data keranjang dari session
$cart = [];
$total = 0;

// Ambil detail menu untuk setiap item di keranjang
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $menuId => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch();

        if ($menu) {
            $subtotal = $menu['harga'] * $quantity;
            $cart[] = [
                'menu_id' => $menu['id'],
                'nama' => $menu['nama'],
                'harga' => $menu['harga'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
    }
}

// Jika keranjang kosong, redirect ke menu
if (empty($cart)) {
    header("Location: /menu/index.php");
    exit();
}

// Tentukan step saat ini
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Proses checkout hanya jika di step 2
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $current_step == 2) {
    try {
        // Mulai transaksi
        $conn->beginTransaction();

        // Validasi input
        $tanggal = $_POST['tanggal'] ?? '';
        $waktu = $_POST['waktu'] ?? '';

        if (empty($tanggal) || empty($waktu)) {
            throw new Exception("Mohon pilih tanggal dan waktu pengambilan");
        }

        $waktu_pengambilan = $tanggal . ' ' . $waktu;

        // Validasi waktu pengambilan
        $waktu_timestamp = strtotime($waktu_pengambilan);
        if ($waktu_timestamp === false || $waktu_timestamp < time()) {
            throw new Exception("Waktu pengambilan tidak valid");
        }

        // Insert ke tabel orders
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_harga, waktu_pengambilan, status) 
            VALUES (?, ?, ?, 'Diproses')
        ");
        $stmt->execute([$_SESSION['user_id'], $total, $waktu_pengambilan]);
        $order_id = $conn->lastInsertId();

        // Insert ke tabel order_items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, menu_id, jumlah, subtotal) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($cart as $item) {
            // Cek stok sekali lagi
            $check_stmt = $conn->prepare("SELECT stok FROM menu WHERE id = ?");
            $check_stmt->execute([$item['menu_id']]);
            $current_stock = $check_stmt->fetchColumn();

            if ($current_stock < $item['quantity']) {
                throw new Exception("Stok tidak mencukupi untuk " . $item['nama']);
            }

            // Insert item pesanan
            $subtotal = $item['harga'] * $item['quantity'];
            $stmt->execute([$order_id, $item['menu_id'], $item['quantity'], $subtotal]);

            // Update stok
            $update_stmt = $conn->prepare("UPDATE menu SET stok = stok - ? WHERE id = ?");
            $update_stmt->execute([$item['quantity'], $item['menu_id']]);
        }

        // Commit transaksi
        $conn->commit();

        // Hapus keranjang
        unset($_SESSION['cart']);

        // Redirect ke step 3 dengan JavaScript
        echo "<script>
            window.location.href = 'checkout.php?step=3&order_id=" . $order_id . "';
        </script>";
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

// Di bagian awal file, setelah session_start()
if (isset($_SESSION['last_order_id']) && !isset($_GET['step'])) {
    $order_id = $_SESSION['last_order_id'];
    unset($_SESSION['last_order_id']);
    header("Location: checkout.php?step=3&order_id=" . $order_id);
    exit();
}

// Generate opsi waktu
$available_times = [];
$start_hour = 8; // Mulai dari jam 8 pagi
$end_hour = 17; // Sampai jam 5 sore

for ($hour = $start_hour; $hour <= $end_hour; $hour++) {
    $time = sprintf("%02d:00", $hour);
    $available_times[] = $time;
}

// Generate opsi tanggal (hari ini dan besok)
$available_dates = [];
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$available_dates[$today] = "Hari ini (" . date('d/m/Y') . ")";
$available_dates[$tomorrow] = "Besok (" . date('d/m/Y', strtotime('+1 day')) . ")";
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <!-- Checkout Steps -->
    <div class="checkout-steps mb-4">
        <div class="step <?= $current_step >= 1 ? 'active' : '' ?>">
            1
            <span class="step-label">Detail Pesanan</span>
        </div>
        <div class="step <?= $current_step >= 2 ? 'active' : '' ?>">
            2
            <span class="step-label">Konfirmasi</span>
        </div>
        <div class="step <?= $current_step == 3 ? 'active' : '' ?>">
            3
            <span class="step-label">Selesai</span>
        </div>
    </div>

    <?php if ($current_step == 1): ?>
        <!-- Step 1: Detail Pesanan -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Detail Pesanan</h4>
                    </div>
                    <div class="card-body">
                        <!-- Tampilkan detail pesanan -->
                        <div class="list-group mb-4">
                            <?php foreach ($cart as $item): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($item['nama']) ?></h6>
                                            <small>Rp <?= number_format($item['harga'], 0, ',', '.') ?> x <?= $item['quantity'] ?></small>
                                        </div>
                                        <div>
                                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Total</h6>
                                    <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="checkout.php?step=2" class="btn btn-primary">
                                Lanjut ke Konfirmasi
                            </a>
                            <a href="/menu/index.php" class="btn btn-outline-secondary">
                                Kembali ke Menu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($current_step == 2): ?>
        <!-- Step 2: Form Konfirmasi -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Konfirmasi Pesanan</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="checkoutForm">
                            <div class="mb-4">
                                <h5>Waktu Pengambilan</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal</label>
                                        <select class="form-select" name="tanggal" required>
                                            <option value="">Pilih tanggal</option>
                                            <?php foreach ($available_dates as $date => $label): ?>
                                                <option value="<?= $date ?>"><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Waktu</label>
                                        <select class="form-select" name="waktu" required>
                                            <option value="">Pilih waktu</option>
                                            <?php foreach ($available_times as $time): ?>
                                                <option value="<?= $time ?>"><?= $time ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="btnKonfirmasi">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                    Konfirmasi Pesanan
                                </button>
                                <a href="checkout.php?step=1" class="btn btn-outline-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($current_step == 3): ?>
        <!-- Step 3: Selesai -->
        <?php
        $order_id = $_GET['order_id'] ?? 0;

        // Update query untuk mengambil detail pesanan
        $stmt = $conn->prepare("
            SELECT o.*, u.nama as nama_pembeli 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();

        // Ambil detail item pesanan
        if ($order) {
            $stmt = $conn->prepare("
                SELECT oi.*, m.nama as nama_menu, m.harga 
                FROM order_items oi 
                JOIN menu m ON oi.menu_id = m.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll();
        }

        if ($order):
        ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="mb-4">Pesanan Berhasil!</h2>
                            <div class="alert alert-success">
                                Nomor Pesanan Anda: <strong>#<?= $order_id ?></strong>
                            </div>

                            <div class="text-start mb-4">
                                <h5 class="mb-3">Detail Pesanan:</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Menu</th>
                                                <th>Jumlah</th>
                                                <th class="text-end">Harga</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                                                    <td><?= $item['jumlah'] ?></td>
                                                    <td class="text-end">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                                    <td class="text-end">Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end"><strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Waktu Pengambilan: <strong><?= date('d/m/Y H:i', strtotime($order['waktu_pengambilan'])) ?></strong>
                            </div>

                            <div class="mt-4">
                                <a href="/menu/index.php" class="btn btn-primary">
                                    <i class="fas fa-utensils me-2"></i>Pesan Lagi
                                </a>
                                <a href="/orders/history.php" class="btn btn-outline-primary ms-2">
                                    <i class="fas fa-history me-2"></i>Lihat Riwayat Pesanan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnKonfirmasi');
        const spinner = btn.querySelector('.spinner-border');

        // Tampilkan loading spinner
        spinner.classList.remove('d-none');
        btn.disabled = true;
    });
</script>

<?php include '../includes/footer.php'; ?>