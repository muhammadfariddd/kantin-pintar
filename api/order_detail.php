<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    try {
        // Ambil detail pesanan
        $stmt = $conn->prepare("
            SELECT o.*, u.nama as pembeli
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND (o.user_id = ? OR ? = true)
        ");
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        $stmt->execute([$_GET['id'], $_SESSION['user_id'], $isAdmin]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Ambil item pesanan
            $stmt = $conn->prepare("
                SELECT oi.*, m.nama, m.harga
                FROM order_items oi
                JOIN menu m ON oi.menu_id = m.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$_GET['id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $order['items'] = $items;

            header('Content-Type: application/json');
            echo json_encode($order);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Order not found']);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
}
