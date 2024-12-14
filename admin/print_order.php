<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    exit('Unauthorized');
}

$order_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("
    SELECT o.*, u.nama as pembeli
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    exit('Order not found');
}

// Ambil detail items
$stmt = $conn->prepare("
    SELECT oi.*, m.nama as nama_menu 
    FROM order_items oi 
    JOIN menu m ON oi.menu_id = m.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Print Order #<?= $order_id ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        th {
            background: #f5f5f5;
        }

        .text-end {
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Order #<?= $order_id ?></h2>
        <p>Pembeli: <?= htmlspecialchars($order['pembeli']) ?></p>
        <p>Waktu Pengambilan: <?= date('d/m/Y H:i', strtotime($order['waktu_pengambilan'])) ?></p>

        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Jumlah</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                        <td><?= $item['jumlah'] ?></td>
                        <td class="text-end">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong></td>
                </tr>
            </tbody>
        </table>

        <button class="no-print" onclick="window.print()">Print</button>
    </div>
</body>

</html>