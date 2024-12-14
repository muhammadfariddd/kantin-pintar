<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: /menu/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

try {
    $conn->beginTransaction();

    // Hitung total
    $total = 0;
    foreach ($_SESSION['cart'] as $menuId => $quantity) {
        $stmt = $conn->prepare("SELECT harga FROM menu WHERE id = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch();
        $total += $menu['harga'] * $quantity;
    }

    // Buat pesanan baru
    $waktuPengambilan = $_POST['tanggal'] . ' ' . $_POST['waktu'];
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_harga, waktu_pengambilan, catatan, status) 
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $total,
        $waktuPengambilan,
        $_POST['catatan'] ?? null
    ]);

    $orderId = $conn->lastInsertId();

    // Simpan detail pesanan
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, menu_id, jumlah, harga) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($_SESSION['cart'] as $menuId => $quantity) {
        $menuStmt = $conn->prepare("SELECT harga FROM menu WHERE id = ?");
        $menuStmt->execute([$menuId]);
        $menu = $menuStmt->fetch();

        $stmt->execute([
            $orderId,
            $menuId,
            $quantity,
            $menu['harga']
        ]);

        // Update stok
        $updateStmt = $conn->prepare("UPDATE menu SET stok = stok - ? WHERE id = ?");
        $updateStmt->execute([$quantity, $menuId]);
    }

    // Kosongkan keranjang
    unset($_SESSION['cart']);

    $conn->commit();

    // Redirect ke halaman sukses
    header("Location: success.php?order_id=" . $orderId);
    exit();
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}
