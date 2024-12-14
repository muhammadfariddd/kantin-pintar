<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
    if ($stmt->execute([$_SESSION['user_id']])) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Gagal menghapus notifikasi');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
