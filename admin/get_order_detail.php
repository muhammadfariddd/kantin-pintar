<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$order_id = $_GET['id'] ?? 0;

try {
    $stmt = $conn->prepare("
        SELECT o.*, u.nama as pembeli
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Ambil items
        $stmt = $conn->prepare("
            SELECT oi.*, m.nama as nama_menu, m.harga
            FROM order_items oi
            JOIN menu m ON oi.menu_id = m.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $order['items'] = $items;
        echo json_encode($order);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
