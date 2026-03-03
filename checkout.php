<?php
require_once 'koneksi.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Ambil data keranjang
$stmt = $conn->prepare("
    SELECT k.id as keranjang_id, k.jumlah, p.id as produk_id, p.nama_produk, p.harga, p.stok
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    WHERE k.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$keranjang = $stmt->fetchAll();

if (empty($keranjang)) {
    redirect('keranjang.php?error=Keranjang%20kosong');
}

// Hitung total
$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Buat transaksi baru
        $stmt = $conn->prepare("INSERT INTO transaksi (user_id, total_harga, status) VALUES (?, ?, 'selesai')");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $transaksi_id = $conn->lastInsertId();
        
        // Simpan detail transaksi dan update stok
        foreach ($keranjang as $item) {
            // Insert detail transaksi
            $stmt = $conn->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, jumlah, harga_saat_ini) VALUES (?, ?, ?, ?)");
            $stmt->execute([$transaksi_id, $item['produk_id'], $item['jumlah'], $item['harga']]);
            
            // Update stok produk
            $stmt = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
            $stmt->execute([$item['jumlah'], $item['produk_id']]);
        }
        
        // Hapus semua item dari keranjang
        $stmt = $conn->prepare("DELETE FROM keranjang WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $conn->commit();
        
        redirect('transaksi.php?success=Transaksi%20berhasil%20dilakukan');
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = 'Terjadi kesalahan. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TokoOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #f97316;
            --dark: #1e293b;
            --light: #f8fafc;
            --white: #ffffff;
            --gray: #64748b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f1f5f9;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo i {
            color: var(--secondary);
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-link:hover {
            color: var(--secondary);
        }
        
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary) 0%, #ea580c 100%);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-lg);
        }
        
        .page-header i {
            font-size: 2.5rem;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
        }
        
        .checkout-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-title i {
            color: var(--primary);
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-info h4 {
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .item-info span {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .item-price {
            font-weight: 600;
            color: var(--primary);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: var(--gray);
        }
        
        .summary-row.total {
            border-top: 2px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .summary-row.total span:last-child {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .user-info {
            margin-bottom: 1.5rem;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            color: var(--dark);
        }
        
        .info-row i {
            color: var(--primary);
            width: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 1rem;
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary) 0%, #ea580c 100%);
            color: var(--white);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-secondary {
            background: var(--light);
            color: var(--dark);
            margin-top: 0.75rem;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .success-animation {
            text-align: center;
            padding: 3rem;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: #166534;
        }
        
        .success-animation h2 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .success-animation p {
            color: var(--gray);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">
                <i class="fas fa-shopping-bag"></i>
                TokoOnline
            </a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="keranjang.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
                <a href="transaksi.php" class="nav-link">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <i class="fas fa-credit-card"></i>
            <div>
                <h1>Checkout</h1>
                <p>Konfirmasi pesanan Anda</p>
            </div>
        </div>

        <div class="checkout-content">
            <div class="checkout-card">
                <h3 class="card-title">
                    <i class="fas fa-shopping-bag"></i> Ringkasan Pesanan
                </h3>
                
                <?php foreach ($keranjang as $item): ?>
                    <div class="order-item">
                        <div class="item-info">
                            <h4><?= htmlspecialchars($item['nama_produk']) ?></h4>
                            <span><?= $item['jumlah'] ?> x <?= formatRupiah($item['harga']) ?></span>
                        </div>
                        <div class="item-price">
                            <?= formatRupiah($item['harga'] * $item['jumlah']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-row">
                    <span>Total Items</span>
                    <span><?= count($keranjang) ?> produk</span>
                </div>
                <div class="summary-row total">
                    <span>Total Pembayaran</span>
                    <span><?= formatRupiah($total) ?></span>
                </div>
            </div>
            
            <div class="checkout-card">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Informasi Pembeli
                </h3>
                
                <div class="user-info">
                    <div class="info-row">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-envelope"></i>
                        <span><?= htmlspecialchars($_SESSION['email']) ?></span>
                    </div>
                </div>
                
                <div class="info-box" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.875rem; color: #0369a1;">
                    <i class="fas fa-info-circle"></i> Stok produk akan dikurangi secara otomatis setelah checkout
                </div>
                
                <form method="POST" action="">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pesanan
                    </button>
                </form>
                <a href="keranjang.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                </a>
            </div>
        </div>
    </div>
</body>
</html>

