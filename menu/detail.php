<?php
session_start();
require_once '../config/database.php';

// Ambil ID menu dari URL
$menu_id = $_GET['id'] ?? 0;

// Ambil detail menu
$stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$menu_id]);
$menu = $stmt->fetch();

// Jika menu tidak ditemukan
if (!$menu) {
    header("Location: index.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="index.php">Menu</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($menu['nama']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <?php if ($menu['gambar']): ?>
                <img src="../assets/img/menu/<?= htmlspecialchars($menu['gambar']) ?>"
                    class="img-fluid rounded"
                    alt="<?= htmlspecialchars($menu['nama']) ?>">
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h1><?= htmlspecialchars($menu['nama']) ?></h1>
            <p class="text-muted"><?= htmlspecialchars($menu['kategori']) ?></p>

            <h3 class="text-primary mb-3">
                Rp <?= number_format($menu['harga'], 0, ',', '.') ?>
            </h3>

            <p class="mb-4"><?= nl2br(htmlspecialchars($menu['deskripsi'])) ?></p>

            <div class="mb-3">
                <label class="form-label">Jumlah</label>
                <div class="input-group" style="width: 150px;">
                    <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
                    <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?= $menu['stok'] ?>">
                    <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(1)">+</button>
                </div>
                <small class="text-muted">Stok tersedia: <?= $menu['stok'] ?></small>
            </div>

            <?php if ($menu['stok'] > 0): ?>
                <button class="btn btn-primary btn-lg" onclick="addToCart()">
                    Tambah ke Keranjang
                </button>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    Stok Habis
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateQuantity(change) {
        const input = document.getElementById('quantity');
        const newValue = parseInt(input.value) + change;
        if (newValue >= 1 && newValue <= <?= $menu['stok'] ?>) {
            input.value = newValue;
        }
    }

    function addToCart() {
        const quantity = parseInt(document.getElementById('quantity').value);
        fetch('../api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    menu_id: <?= $menu_id ?>,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Berhasil ditambahkan ke keranjang!');
                    window.location.href = 'index.php';
                } else {
                    alert(data.message);
                }
            });
    }
</script>

<?php include '../includes/footer.php'; ?>