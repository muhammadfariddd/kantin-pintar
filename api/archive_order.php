<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['order_id'])) {
    try {
        // Pastikan pesanan milik user yang sedang login
        $stmt = $conn->prepare("
            UPDATE orders 
            SET is_archived = 1 
            WHERE id = ? AND user_id = ? AND status = 'Diambil'
        ");

        if ($stmt->execute([$data['order_id'], $_SESSION['user_id']])) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Gagal mengarsipkan pesanan');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID pesanan tidak valid'
    ]);
}
