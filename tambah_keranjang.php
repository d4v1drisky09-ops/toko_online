<?php
require_once 'koneksi.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$produk_id = $_GET['id'];

// Cek produk ada dan stok tersedia
$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$produk_id]);
$produk = $stmt->fetch();

if (!$produk) {
    redirect('index.php?error=Produk%20tidak%20ditemukan');
}

if ($produk['stok'] <= 0) {
    redirect('index.php?error=Stok%20produk%20habis');
}

// Cek apakah produk sudah ada di keranjang
$stmt = $conn->prepare("SELECT * FROM keranjang WHERE user_id = ? AND produk_id = ?");
$stmt->execute([$_SESSION['user_id'], $produk_id]);
$existing_item = $stmt->fetch();

if ($existing_item) {
    // Update jumlah jika belum melebihi stok
    $new_jumlah = $existing_item['jumlah'] + 1;
    if ($new_jumlah <= $produk['stok']) {
        $stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
        $stmt->execute([$new_jumlah, $existing_item['id']]);
    }
} else {
    // Tambah produk baru ke keranjang
    $stmt = $conn->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?, ?, 1)");
    $stmt->execute([$_SESSION['user_id'], $produk_id]);
}

redirect('keranjang.php?success=Produk%20ditambahkan%20ke%20keranjang');
?>

