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
$action = $data['action'] ?? '';
$value = $data['value'] ?? null;

// Validasi menu
$stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$menuId]);
$menu = $stmt->fetch();

if (!$menu) {
    echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
    exit;
}

// Update quantity
switch ($action) {
    case 'increase':
        if ($_SESSION['cart'][$menuId] < $menu['stok']) {
            $_SESSION['cart'][$menuId]++;
        } else {
            echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
            exit;
        }
        break;

    case 'decrease':
        if ($_SESSION['cart'][$menuId] > 1) {
            $_SESSION['cart'][$menuId]--;
        }
        break;

    case 'set':
        if ($value > 0 && $value <= $menu['stok']) {
            $_SESSION['cart'][$menuId] = $value;
        } else {
            echo json_encode(['success' => false, 'message' => 'Jumlah tidak valid']);
            exit;
        }
        break;
}

echo json_encode(['success' => true]);
