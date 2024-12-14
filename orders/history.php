<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Filter untuk menampilkan pesanan
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'active';
$archive_after_days = 7; // Arsipkan setelah 7 hari

// Auto arsip pesanan yang sudah diambil lebih dari 7 hari
$stmt = $conn->prepare("
    UPDATE orders 
    SET is_archived = 1 
    WHERE status = 'Diambil' 
    AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
    AND is_archived = 0
    AND user_id = ?
");
$stmt->execute([$archive_after_days, $_SESSION['user_id']]);

// Ambil riwayat pesanan
$stmt = $conn->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(m.nama, ' (', oi.jumlah, ')') SEPARATOR ', ') as items
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN menu m ON oi.menu_id = m.id
    WHERE o.user_id = ? AND o.is_archived = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $filter === 'archived' ? 1 : 0]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-4">
    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <h2 class="mb-0">Riwayat Pesanan</h2>
        </div>
        <div class="col-lg-6">
            <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                <div class="btn-group">
                    <a href="?filter=active" class="btn btn-outline-primary <?= $filter === 'active' ? 'active' : '' ?>">
                        <i class="fas fa-clock me-2"></i>Pesanan Aktif
                    </a>
                    <a href="?filter=archived" class="btn btn-outline-secondary <?= $filter === 'archived' ? 'active' : '' ?>">
                        <i class="fas fa-archive me-2"></i>Arsip
                    </a>
                </div>
                <a href="/menu/index.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Pesan Lagi
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <h4>Belum ada riwayat pesanan</h4>
            <p class="text-muted">Anda belum pernah melakukan pemesanan</p>
            <a href="/menu/index.php" class="btn btn-primary">
                Mulai Pesan Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-md-6 mb-4">
                    <div class="card order-card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Order #<?= $order['id'] ?></span>
                            <?php
                            $status_class = match ($order['status']) {
                                'Diproses' => 'warning',
                                'Siap' => 'success',
                                'Diambil' => 'primary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $status_class ?>">
                                <?= $order['status'] ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Waktu Pemesanan:</small>
                                <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Waktu Pengambilan:</small>
                                <span><?= date('d/m/Y H:i', strtotime($order['waktu_pengambilan'])) ?></span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Items:</small>
                                <span><?= htmlspecialchars($order['items']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Total:</small>
                                    <span class="fs-5 fw-bold text-primary d-block">
                                        Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm"
                                        onclick="viewOrderDetail(<?= $order['id'] ?>)">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </button>
                                    <?php if ($order['status'] === 'Diambil' && !$order['is_archived']): ?>
                                        <button class="btn btn-outline-secondary btn-sm"
                                            onclick="archiveOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-archive me-1"></i>Arsip
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<style>
    .order-card {
        transition: transform 0.2s;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .order-card:hover {
        transform: translateY(-5px);
    }

    .badge {
        padding: 0.5em 1em;
    }

    @media (max-width: 575.98px) {
        .btn-group {
            width: 100%;
            display: flex;
        }

        .btn-group .btn {
            flex: 1;
        }
    }

    @media (min-width: 576px) and (max-width: 991.98px) {
        .btn-group {
            min-width: 200px;
        }
    }

    @media (min-width: 992px) {
        .btn-group {
            width: auto;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>

<script>
    function viewOrderDetail(orderId) {
        fetch(`/api/order_detail.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const modalContent = `
                    <div class="modal-body">
                        <h5 class="mb-3">Detail Pesanan #${orderId}</h5>
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
                                    ${data.items.map(item => `
                                        <tr>
                                            <td>${item.nama}</td>
                                            <td>${item.jumlah}</td>
                                            <td class="text-end">Rp ${formatNumber(item.harga)}</td>
                                            <td class="text-end">Rp ${formatNumber(item.jumlah * item.harga)}</td>
                                        </tr>
                                    `).join('')}
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong>Rp ${formatNumber(data.total_harga)}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Waktu Pengambilan: <strong>${formatDate(data.waktu_pengambilan)}</strong>
                        </div>
                    </div>
                `;

                document.getElementById('orderDetailContent').innerHTML = modalContent;
                new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil detail pesanan');
            });
    }

    function archiveOrder(orderId) {
        if (confirm('Arsipkan pesanan ini?')) {
            fetch('/api/archive_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tampilkan notifikasi sukses
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
                        alert.style.zIndex = '1050';
                        alert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>Pesanan berhasil diarsipkan
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                        document.body.appendChild(alert);

                        // Refresh halaman setelah 1 detik
                        setTimeout(() => {
                            window.location.href = '?filter=archived';
                        }, 1000);
                    } else {
                        alert(data.message || 'Gagal mengarsipkan pesanan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengarsipkan pesanan');
                });
        }
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
</script>