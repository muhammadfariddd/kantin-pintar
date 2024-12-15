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

if (isset($_SESSION['cart'][$menuId])) {
    unset($_SESSION['cart'][$menuId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
}
