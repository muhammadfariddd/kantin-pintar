<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $menuId => $quantity) {
        $stmt = $conn->prepare("SELECT id, nama, harga FROM menu WHERE id = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch();

        if ($menu) {
            $items[] = [
                'id' => $menu['id'],
                'nama' => $menu['nama'],
                'harga' => $menu['harga'],
                'quantity' => $quantity
            ];
            $total += $menu['harga'] * $quantity;
        }
    }
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total
]);
