<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Terima data JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    try {
        // Ambil info gambar sebelum menghapus
        $stmt = $conn->prepare("SELECT gambar FROM menu WHERE id = ?");
        $stmt->execute([$data['id']]);
        $menu = $stmt->fetch();

        // Hapus menu dari database
        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->execute([$data['id']]);

        // Hapus file gambar jika ada
        if ($menu['gambar']) {
            $file_path = "../assets/img/menu/" . $menu['gambar'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID menu tidak ditemukan']);
}
