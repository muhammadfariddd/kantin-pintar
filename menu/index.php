<?php
session_start();
require_once '../config/database.php';

// Ambil kategori yang aktif dari query string
$active_category = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Ambil daftar menu dari database
$query = "SELECT * FROM menu WHERE stok > 0";
if (!empty($active_category)) {
    $query .= " AND kategori = :kategori";
}
$query .= " ORDER BY nama";

$stmt = $conn->prepare($query);
if (!empty($active_category)) {
    $stmt->bindParam(':kategori', $active_category);
}
$stmt->execute();
$menus = $stmt->fetchAll();

// Ambil kategori untuk filter
$stmt = $conn->query("SELECT DISTINCT kategori FROM menu ORDER BY kategori");
$categories = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Filter Kategori -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Kategori</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="?kategori="
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $active_category === '' ? 'active' : '' ?>">
                            <span><i class="fas fa-th-large me-2"></i>Semua Menu</span>
                            <span class="badge bg-primary rounded-pill">
                                <?= count($menus) ?>
                            </span>
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="?kategori=<?= urlencode($category['kategori']) ?>"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $active_category === $category['kategori'] ? 'active' : '' ?>">
                                <span>
                                    <i class="fas <?= match ($category['kategori']) {
                                                        'makanan' => 'fa-utensils',
                                                        'minuman' => 'fa-glass-cheers',
                                                        'snack' => 'fa-cookie',
                                                        default => 'fa-circle'
                                                    } ?> me-2"></i>
                                    <?= ucfirst(htmlspecialchars($category['kategori'])) ?>
                                </span>
                                <?php
                                $count = array_reduce($menus, function ($carry, $menu) use ($category) {
                                    return $carry + ($menu['kategori'] === $category['kategori'] ? 1 : 0);
                                }, 0);
                                ?>
                                <span class="badge bg-primary rounded-pill"><?= $count ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                    <p class="card-text small">
                        Silakan pilih menu yang Anda inginkan. Pastikan untuk memeriksa stok yang tersedia sebelum melakukan pemesanan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Daftar Menu -->
        <div class="col-md-9">
            <!-- Search Bar -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchMenu"
                            placeholder="Cari menu..." onkeyup="filterMenu(this.value)">
                    </div>
                </div>
            </div>

            <!-- Menu Grid -->
            <div class="menu-grid" id="menuGrid">
                <?php foreach ($menus as $menu): ?>
                    <div class="menu-card" data-name="<?= strtolower($menu['nama']) ?>">
                        <div class="card-img-wrapper">
                            <?php if ($menu['gambar']): ?>
                                <img src="/assets/img/menu/<?= htmlspecialchars($menu['gambar']) ?>"
                                    class="card-img-top"
                                    alt="<?= htmlspecialchars($menu['nama']) ?>">
                            <?php endif; ?>
                            <?php if ($menu['stok'] <= 5): ?>
                                <div class="stock-badge bg-warning">
                                    Stok Terbatas
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?= htmlspecialchars($menu['nama']) ?></h5>
                                <span class="badge bg-<?= $menu['stok'] > 5 ? 'success' : 'warning' ?>">
                                    Stok: <?= $menu['stok'] ?>
                                </span>
                            </div>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars($menu['deskripsi']) ?>
                            </p>
                            <div class="price mb-3">
                                Rp <?= number_format($menu['harga'], 0, ',', '.') ?>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn-add-cart" onclick="addToCart(<?= $menu['id'] ?>)">
                                    <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                                </button>
                            <?php else: ?>
                                <a href="/auth/login.php" class="btn-add-cart">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login untuk Memesan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Keranjang -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cartContent">
                <!-- Isi keranjang akan dimuat disini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                </button>
                <a href="/orders/cart.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-2"></i>Lihat Keranjang
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Tambahan CSS untuk menu */
    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        color: #000;
    }

    .menu-card .card-img-wrapper {
        position: relative;
    }

    .menu-card .price {
        color: #4169e1;
        font-size: 1.2rem;
        font-weight: 600;
    }

    .btn-add-cart {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        background: #4169e1;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-add-cart:hover {
        background: #3557c5;
        transform: translateY(-2px);
    }

    /* Search bar styling */
    .input-group-text {
        border-radius: 8px 0 0 8px;
    }

    #searchMenu {
        border-radius: 0 8px 8px 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .menu-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
    }
</style>

<script>
    // Fungsi pencarian menu
    function filterMenu(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        const menuCards = document.querySelectorAll('.menu-card');

        menuCards.forEach(card => {
            const menuName = card.dataset.name.toLowerCase();
            if (menuName.includes(searchTerm)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Fungsi tambah ke keranjang
    function addToCart(menuId) {
        fetch('/api/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    menu_id: menuId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update tampilan keranjang
                    updateCartModal();
                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById('cartModal')).show();
                } else {
                    alert(data.message || 'Gagal menambahkan ke keranjang');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menambahkan ke keranjang');
            });
    }

    // Fungsi update modal keranjang
    function updateCartModal() {
        fetch('/api/get_cart.php')
            .then(response => response.json())
            .then(data => {
                const cartContent = document.getElementById('cartContent');
                if (data.items.length === 0) {
                    cartContent.innerHTML = '<p class="text-center">Keranjang kosong</p>';
                    return;
                }

                let html = '<div class="list-group">';
                let total = 0;

                data.items.forEach(item => {
                    const subtotal = item.harga * item.quantity;
                    total += subtotal;
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">${item.nama}</h6>
                                    <small>Rp ${item.harga.toLocaleString()} x ${item.quantity}</small>
                                </div>
                                <div>Rp ${subtotal.toLocaleString()}</div>
                            </div>
                        </div>
                    `;
                });

                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total:</strong>
                            <strong>Rp ${total.toLocaleString()}</strong>
                        </div>
                    </div>
                `;

                cartContent.innerHTML = html;
            });
    }
</script>

<?php include '../includes/footer.php'; ?>