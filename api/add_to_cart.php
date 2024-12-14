<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$menuId = $data['menu_id'] ?? 0;
$quantity = $data['quantity'] ?? 1;

// Validasi menu
$stmt = $conn->prepare("SELECT * FROM menu WHERE id = ? AND stok >= ?");
$stmt->execute([$menuId, $quantity]);
$menu = $stmt->fetch();

if (!$menu) {
    echo json_encode(['success' => false, 'message' => 'Menu tidak tersedia atau stok tidak cukup']);
    exit;
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tambah ke keranjang atau update quantity
if (isset($_SESSION['cart'][$menuId])) {
    $_SESSION['cart'][$menuId] += $quantity;
} else {
    $_SESSION['cart'][$menuId] = $quantity;
}

echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke keranjang']);
