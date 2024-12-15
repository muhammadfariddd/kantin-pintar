<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['order_id']) && isset($data['status'])) {
    try {
        $conn->beginTransaction();

        // Validasi status
        $allowed_status = ['Diproses', 'Siap', 'Diambil'];
        if (!in_array($data['status'], $allowed_status)) {
            throw new Exception('Status tidak valid');
        }

        // Update status pesanan
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if (!$stmt->execute([$data['status'], $data['order_id']])) {
            throw new Exception('Gagal mengupdate status');
        }

        // Jika status diubah menjadi 'Siap', buat notifikasi
        if ($data['status'] === 'Siap') {
            // Ambil user_id dari pesanan
            $stmt = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
            $stmt->execute([$data['order_id']]);
            $order = $stmt->fetch();

            if ($order) {
                // Buat notifikasi sesuai struktur tabel yang ada
                $pesan = "Pesanan #" . $data['order_id'] . " Anda sudah siap untuk diambil!";
                $stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, pesan, status) 
                    VALUES (?, ?, 0)
                ");
                $stmt->execute([$order['user_id'], $pesan]);
            }
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
}
