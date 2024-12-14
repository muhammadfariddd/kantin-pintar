<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kantin Pintar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/admin-style.css" rel="stylesheet">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h4>Kantin Pintar</h4>
                <small>Admin Panel</small>
            </div>
            <ul class="sidebar-menu">
                <?php
                $current_page = basename($_SERVER['PHP_SELF'], '.php');
                ?>
                <li>
                    <a href="/admin/index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="/admin/menu.php" class="<?= $current_page === 'menu' ? 'active' : '' ?>">
                        <i class="fas fa-utensils"></i> Menu Makanan
                    </a>
                </li>
                <li>
                    <a href="/admin/orders.php" class="<?= $current_page === 'orders' ? 'active' : '' ?>">
                        <i class="fas fa-shopping-cart"></i> Pesanan
                    </a>
                </li>
                <li>
                    <a href="/admin/reports.php" class="<?= $current_page === 'reports' ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <div class="main-content" id="main-content">
            <nav class="top-navbar">
                <div class="navbar-left">
                    <button class="btn-toggle-sidebar" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <div class="navbar-right">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/admin/profile.php">
                                    <i class="fas fa-user-circle me-2"></i>Profil
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