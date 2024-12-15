<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Update semua notifikasi yang belum dibaca menjadi sudah dibaca
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET status = 1 
        WHERE user_id = ? AND status = 0
    ");

    if ($stmt->execute([$_SESSION['user_id']])) {
        echo json_encode([
            'success' => true,
            'message' => 'Semua notifikasi telah ditandai sudah dibaca'
        ]);
    } else {
        throw new Exception('Gagal mengupdate status notifikasi');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
