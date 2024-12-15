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
                        <div class="d-flex align-items-center">
                            <!-- Notifications Dropdown -->
                            <div class="notifications-dropdown me-3">
                                <button class="btn btn-link position-relative" id="notificationsDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell fs-5"></i>
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 0");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $unread = $stmt->fetchColumn();
                                    if ($unread > 0):
                                    ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?= $unread ?>
                                        </span>
                                    <?php endif; ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end notification-menu">
                                    <div class="notification-header d-flex justify-content-between align-items-center p-3">
                                        <h6 class="mb-0">Notifikasi</h6>
                                        <?php if ($unread > 0): ?>
                                            <button class="btn btn-link btn-sm text-decoration-none" onclick="markAllAsRead()">
                                                Tandai sudah dibaca
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-list">
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT * FROM notifications 
                                            WHERE user_id = ? 
                                            ORDER BY created_at DESC 
                                            LIMIT 10
                                        ");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $notifications = $stmt->fetchAll();

                                        if (empty($notifications)):
                                        ?>
                                            <div class="text-center p-3">
                                                <p class="text-muted mb-0">Tidak ada notifikasi</p>
                                            </div>
                                            <?php else:
                                            foreach ($notifications as $notif):
                                                // Pastikan order_id ada dan valid
                                                $order_id = isset($notif['order_id']) ? intval($notif['order_id']) : 0;
                                            ?>
                                                <div class="notification-item <?= $notif['status'] == 0 ? 'unread' : '' ?>"
                                                    data-notif-id="<?= $notif['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div onclick="viewOrderFromNotif(<?= $order_id ?>, <?= $notif['id'] ?>)">
                                                            <p class="mb-1"><?= htmlspecialchars($notif['pesan']) ?></p>
                                                            <small class="text-muted">
                                                                <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                                            </small>
                                                        </div>
                                                        <button class="btn btn-sm text-danger"
                                                            onclick="deleteNotification(<?= $notif['id'] ?>, event)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                        <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            </div>
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
                    </div>
                </nav>
            <?php endif; ?>

            <!-- Content Area -->
            <div class="content-area">