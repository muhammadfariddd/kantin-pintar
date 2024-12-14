<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Tambah kolom is_archived jika belum ada
try {
    // Cek apakah kolom sudah ada
    $stmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'is_archived'");
    if ($stmt->rowCount() == 0) {
        // Jika belum ada, tambahkan kolom baru
        $conn->exec("ALTER TABLE orders ADD COLUMN is_archived TINYINT(1) DEFAULT 0");
    }
} catch (PDOException $e) {
    // Handle error jika perlu
    error_log("Error adding column: " . $e->getMessage());
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
");
$stmt->execute([$archive_after_days]);

// Query untuk mengambil pesanan
$query = "
    SELECT 
        o.*,
        u.nama as pembeli,
        COUNT(oi.id) as total_items,
        GROUP_CONCAT(CONCAT(m.nama, ' (', oi.jumlah, ')') SEPARATOR ', ') as items_detail
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN menu m ON oi.menu_id = m.id
    WHERE o.is_archived = ?
    GROUP BY o.id 
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute([$filter === 'archived' ? 1 : 0]);
$orders = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Filter Section -->
    <div class="order-filter">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Manajemen Pesanan</h4>
            <div>
                <div class="btn-group me-2">
                    <a href="?filter=active" class="btn btn-outline-primary <?= $filter === 'active' ? 'active' : '' ?>">
                        Pesanan Aktif
                    </a>
                    <a href="?filter=archived" class="btn btn-outline-secondary <?= $filter === 'archived' ? 'active' : '' ?>">
                        Arsip
                    </a>
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline-primary active" data-filter="all">
                        Semua <span class="badge bg-primary ms-1"><?= count($orders) ?></span>
                    </button>
                    <button class="btn btn-outline-warning" data-filter="Diproses">
                        Diproses <span class="badge bg-warning text-dark ms-1" id="countDiproses">0</span>
                    </button>
                    <button class="btn btn-outline-success" data-filter="Siap">
                        Siap <span class="badge bg-success ms-1" id="countSiap">0</span>
                    </button>
                    <button class="btn btn-outline-info" data-filter="Diambil">
                        Diambil <span class="badge bg-info ms-1" id="countDiambil">0</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card order-table mt-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pembeli</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Waktu Pengambilan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="order-row" data-status="<?= $order['status'] ?>">
                            <td class="align-middle">
                                <strong>#<?= $order['id'] ?></strong>
                            </td>
                            <td class="align-middle">
                                <?= htmlspecialchars($order['pembeli']) ?>
                            </td>
                            <td class="align-middle">
                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                    data-bs-toggle="tooltip" title="<?= htmlspecialchars($order['items_detail']) ?>">
                                    <?= htmlspecialchars($order['items_detail']) ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
                            </td>
                            <td class="align-middle">
                                <?= date('d/m/Y H:i', strtotime($order['waktu_pengambilan'])) ?>
                            </td>
                            <td class="align-middle">
                                <select class="status-select"
                                    onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)"
                                    data-order-id="<?= $order['id'] ?>">
                                    <option value="Diproses" <?= $order['status'] == 'Diproses' ? 'selected' : '' ?>>
                                        Diproses
                                    </option>
                                    <option value="Siap" <?= $order['status'] == 'Siap' ? 'selected' : '' ?>>
                                        Siap Diambil
                                    </option>
                                    <option value="Diambil" <?= $order['status'] == 'Diambil' ? 'selected' : '' ?>>
                                        Sudah Diambil
                                    </option>
                                </select>
                            </td>
                            <td class="align-middle">
                                <div class="order-actions">
                                    <button class="btn btn-info btn-sm" onclick="viewOrderDetail(<?= $order['id'] ?>)">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="printOrder(<?= $order['id'] ?>)">
                                        <i class="fas fa-print me-1"></i> Cetak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        updateOrderCounts();

        // Simpan nilai awal status untuk setiap select
        document.querySelectorAll('.status-select').forEach(select => {
            select.setAttribute('data-original-value', select.value);
            select.setAttribute('data-order-id', select.closest('tr').dataset.orderId);
        });
    });

    // Update order counts
    function updateOrderCounts() {
        const rows = document.querySelectorAll('.order-row');
        let counts = {
            'Diproses': 0,
            'Siap': 0,
            'Diambil': 0
        };

        rows.forEach(row => {
            const status = row.dataset.status;
            if (counts.hasOwnProperty(status)) {
                counts[status]++;
            }
        });

        Object.keys(counts).forEach(status => {
            const element = document.getElementById(`count${status}`);
            if (element) {
                element.textContent = counts[status];
            }
        });
    }

    // Filter orders
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;

            // Update active button
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Filter rows
            document.querySelectorAll('.order-row').forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    function updateOrderStatus(orderId, newStatus) {
        if (confirm(`Ubah status pesanan #${orderId} menjadi "${newStatus}"?`)) {
            fetch('/admin/update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tampilkan notifikasi
                        showNotification('Status pesanan berhasil diperbarui', 'success');

                        // Update tampilan
                        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                        if (row) {
                            row.dataset.status = newStatus;
                            updateOrderCounts();
                        }

                        // Reload halaman setelah 1 detik
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message || 'Gagal mengubah status', 'error');

                        // Kembalikan select ke nilai sebelumnya
                        const select = document.querySelector(`select[data-order-id="${orderId}"]`);
                        if (select) {
                            select.value = select.getAttribute('data-original-value');
                        }
                    }
                })
                .catch(error => {
                    showNotification('Terjadi kesalahan', 'error');
                    console.error('Error:', error);
                });
        } else {
            // Jika dibatalkan, kembalikan select ke nilai sebelumnya
            const select = document.querySelector(`select[data-order-id="${orderId}"]`);
            if (select) {
                select.value = select.getAttribute('data-original-value');
            }
        }
    }

    function viewOrderDetail(orderId) {
        fetch(`/admin/get_order_detail.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                const modalContent = document.getElementById('orderDetailContent');
                modalContent.innerHTML = `
                    <div class="mb-3">
                        <h6>Informasi Pembeli:</h6>
                        <p class="mb-1">Nama: ${data.pembeli}</p>
                        <p class="mb-1">Waktu Pemesanan: ${formatDate(data.created_at)}</p>
                        <p>Waktu Pengambilan: ${formatDate(data.waktu_pengambilan)}</p>
                    </div>
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
                                        <td>${item.nama_menu}</td>
                                        <td>${item.jumlah}</td>
                                        <td class="text-end">Rp ${formatNumber(item.harga)}</td>
                                        <td class="text-end">Rp ${formatNumber(item.subtotal)}</td>
                                    </tr>
                                `).join('')}
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>Rp ${formatNumber(data.total_harga)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
            });
    }

    function printOrder(orderId) {
        // Buka window baru untuk print
        const printWindow = window.open(`/admin/print_order.php?id=${orderId}`, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
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

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function showNotification(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alert.style.zIndex = '1050';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    // Tambah fungsi untuk mengarsipkan pesanan
    function archiveOrder(orderId) {
        if (confirm('Arsipkan pesanan ini?')) {
            fetch('/admin/archive_order.php', {
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
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal mengarsipkan pesanan');
                    }
                });
        }
    }
</script>

<?php include 'includes/footer.php'; ?>