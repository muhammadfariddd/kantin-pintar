<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

if ($_SESSION['user_role'] === 'admin') {
    header("Location: /admin/index.php");
    exit();
}

require_once 'config/database.php';

// Ambil menu populer
$stmt = $conn->query("
    SELECT m.*, COUNT(oi.id) as order_count 
    FROM menu m 
    LEFT JOIN order_items oi ON m.id = oi.menu_id 
    WHERE m.stok > 0 
    GROUP BY m.id 
    ORDER BY order_count DESC 
    LIMIT 4
");
$popular_menu = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Enhanced Parallax Hero Section -->
<div class="parallax-section">
    <div class="parallax-bg"></div>
    <div class="parallax-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    <div class="parallax-content">
        <div class="parallax-floating">
            <h1>Selamat Datang di Kantin Pintar</h1>
            <p>Pesan makanan dan minuman favorit Anda dengan mudah dan cepat</p>
            <div class="mt-4">
                <a href="/menu/index.php" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-utensils me-2"></i>Lihat Menu
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Floating Stats -->
<div class="container">
    <div class="floating-stats">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-value">20+ Menu</div>
                    <div class="stat-label">Pilihan Makanan & Minuman</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">5-15 Menit</div>
                    <div class="stat-label">Waktu Penyajian</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <div class="stat-value">100+</div>
                    <div class="stat-label">Pelanggan Puas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Populer Section -->
    <section class="mb-5">
        <h2 class="mb-4">Menu Populer</h2>
        <div class="row g-4">
            <?php foreach ($popular_menu as $menu): ?>
                <div class="col-md-3">
                    <div class="card h-100 menu-card">
                        <?php if ($menu['gambar']): ?>
                            <img src="/assets/img/menu/<?= htmlspecialchars($menu['gambar']) ?>"
                                class="card-img-top menu-img"
                                alt="<?= htmlspecialchars($menu['nama']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($menu['nama']) ?></h5>
                                <span class="badge bg-<?= $menu['stok'] > 5 ? 'success' : 'warning' ?>">
                                    Stok: <?= $menu['stok'] ?>
                                </span>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                <?= htmlspecialchars($menu['deskripsi']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold text-primary">
                                    Rp <?= number_format($menu['harga'], 0, ',', '.') ?>
                                </span>
                                <a href="/menu/detail.php?id=<?= $menu['id'] ?>"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-shopping-cart me-1"></i>Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Kategori Section -->
    <section class="mb-5">
        <h2 class="mb-4">Kategori</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <a href="/menu/index.php?kategori=makanan" class="text-decoration-none">
                    <div class="card category-card bg-primary text-white">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-utensils fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0">Makanan</h3>
                                <small>Lihat semua makanan</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/menu/index.php?kategori=minuman" class="text-decoration-none">
                    <div class="card category-card bg-success text-white">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-glass-cheers fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0">Minuman</h3>
                                <small>Lihat semua minuman</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="/menu/index.php?kategori=snack" class="text-decoration-none">
                    <div class="card category-card bg-warning text-white">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-cookie fa-2x me-3"></i>
                            <div>
                                <h3 class="mb-0">Snack</h3>
                                <small>Lihat semua snack</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>