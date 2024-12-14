<?php
require_once '../config/database.php';

try {
    $conn->beginTransaction();

    // 1. Ambil data admin dari tabel users
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'admin' AND nim = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        // 2. Insert ke tabel admin
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$admin['nim'], $admin['password']]);

        // 3. Hapus admin dari tabel users
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$admin['id']]);

        // 4. Update auto increment users jika perlu
        $stmt = $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");

        $conn->commit();
        echo "Migrasi admin berhasil!";
    } else {
        throw new Exception("Data admin tidak ditemukan!");
    }
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
