<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Ambil data keranjang
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $menuId => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch();

        if ($menu) {
            $subtotal = $menu['harga'] * $quantity;
            $cart_items[] = [
                'id' => $menu['id'],
                'nama' => $menu['nama'],
                'harga' => $menu['harga'],
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Keranjang Belanja</h2>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Keranjang belanja kosong.
            <a href="/menu/index.php" class="alert-link">Kembali ke Menu</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Menu</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['nama']) ?></td>
                                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <div class="input-group" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary btn-sm"
                                                        onclick="updateQuantity(<?= $item['id'] ?>, 'decrease')">-</button>
                                                    <input type="number" class="form-control form-control-sm text-center"
                                                        value="<?= $item['quantity'] ?>"
                                                        min="1"
                                                        onchange="updateQuantity(<?= $item['id'] ?>, 'set', this.value)">
                                                    <button class="btn btn-outline-secondary btn-sm"
                                                        onclick="updateQuantity(<?= $item['id'] ?>, 'increase')">+</button>
                                                </div>
                                            </td>
                                            <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                            <td>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="removeItem(<?= $item['id'] ?>)">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Pesanan</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total</span>
                            <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong>
                        </div>
                        <a href="/orders/checkout.php" class="btn btn-primary w-100">
                            Lanjut ke Pembayaran
                        </a>
                        <a href="/menu/index.php" class="btn btn-outline-secondary w-100 mt-2">
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function updateQuantity(menuId, action, value = null) {
        let url = '/orders/update_cart.php';
        let data = {
            menu_id: menuId,
            action: action
        };

        if (value !== null) {
            data.value = parseInt(value);
        }

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }

    function removeItem(menuId) {
        if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            fetch('/orders/remove_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        menu_id: menuId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    }
</script>

<?php include '../includes/footer.php'; ?>