<?php
require_once '../config/database.php';

try {
    // Hash password default
    $password = password_hash('password', PASSWORD_DEFAULT);

    // Insert admin baru
    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $password]);

    echo "Admin berhasil dibuat!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
