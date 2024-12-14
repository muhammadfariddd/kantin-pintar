<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kantin Pintar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <div class="main-container d-flex">
        <!-- Sidebar -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <h4>Kantin Pintar</h4>
                    <small><?= $_SESSION['user_role'] == 'admin' ? 'Admin Panel' : 'User Panel' ?></small>
                </div>
                <ul class="sidebar-menu">
                    <?php if ($_SESSION['user_role'] == 'mahasiswa'):
                        // Get current page
                        $current_page = basename($_SERVER['PHP_SELF']);
                        $current_dir = basename(dirname($_SERVER['PHP_SELF']));
                    ?>
                        <li>
                            <a href="/index.php" class="<?= $current_page === 'index.php' && $current_dir === '' ? 'active' : '' ?>">
                                <i class="fas fa-home"></i> Beranda
                            </a>
                        </li>
                        <li>
                            <a href="/menu/index.php" class="<?= $current_dir === 'menu' ? 'active' : '' ?>">
                                <i class="fas fa-utensils"></i> Menu
                            </a>
                        </li>
                        <li>
                            <a href="/orders/cart.php" class="<?= $current_page === 'cart.php' ? 'active' : '' ?>">
                                <i class="fas fa-shopping-cart"></i> Keranjang
                            </a>
                        </li>
                        <li>
                            <a href="/orders/history.php" class="<?= $current_page === 'history.php' ? 'active' : '' ?>">
                                <i class="fas fa-history"></i> Riwayat Pesanan
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <nav class="top-navbar">
                    <div class="navbar-left">
                        <button class="btn-toggle-sidebar" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>

                    <div class="navbar-right">
                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user fs-5"></i>
                                <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/user/settings.php">
                                        <i class="fas fa-user-circle me-2"></i>Profile
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/auth/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>

            <!-- Content Area -->
            <div class="content-area">