<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['notification_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$data['notification_id'], $_SESSION['user_id']])) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Gagal menghapus notifikasi');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
}
