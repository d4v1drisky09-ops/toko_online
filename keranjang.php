<?php
require_once 'koneksi.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Update jumlah di keranjang
if (isset($_POST['update_jumlah'])) {
    $keranjang_id = $_POST['keranjang_id'];
    $jumlah = (int)$_POST['jumlah'];
    
    if ($jumlah <= 0) {
        $stmt = $conn->prepare("DELETE FROM keranjang WHERE id = ?");
        $stmt->execute([$keranjang_id]);
    } else {
        $stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
        $stmt->execute([$jumlah, $keranjang_id]);
    }
    redirect('keranjang.php');
}

// Hapus item dari keranjang
if (isset($_GET['hapus'])) {
    $keranjang_id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id = ? AND user_id = ?");
    $stmt->execute([$keranjang_id, $_SESSION['user_id']]);
    redirect('keranjang.php?success=Item%20dihapus%20dari%20keranjang');
}

// Ambil data keranjang dengan informasi produk
$stmt = $conn->prepare("
    SELECT k.id as keranjang_id, k.jumlah, p.id as produk_id, p.nama_produk, p.harga, p.stok, p.gambar
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    WHERE k.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$keranjang = $stmt->fetchAll();

// Hitung total
$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - TokoOnline</title>
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
        
        /* Navbar */
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
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
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
        
        .page-header p {
            opacity: 0.9;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        
        @media (max-width: 900px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
        }
        
        .cart-items {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .cart-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            align-items: center;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--gray);
            flex-shrink: 0;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            color: var(--primary);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .item-stok {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.25rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            width: 36px;
            height: 36px;
            border: 2px solid var(--primary);
            background: var(--white);
            color: var(--primary);
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .quantity-btn:hover {
            background: var(--primary);
            color: var(--white);
        }
        
        .quantity-input {
            width: 60px;
            height: 36px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-delete {
            padding: 0.5rem 1rem;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.3s;
        }
        
        .btn-delete:hover {
            background: #fecaca;
        }
        
        /* Cart Summary */
        .cart-summary {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .summary-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
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
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }
        
        .empty-cart i {
            font-size: 5rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }
        
        .empty-cart h3 {
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
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
                <?php if (isAdmin()): ?>
                    <a href="admin.php" class="nav-link">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                <?php endif; ?>
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
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <i class="fas fa-shopping-cart"></i>
            <div>
                <h1>Keranjang Belanja</h1>
                <p>Kelola produk yang ingin Anda beli</p>
            </div>
        </div>

        <?php if (empty($keranjang)): ?>
            <div class="cart-items">
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>Keranjang Anda Kosong</h3>
                    <p>Silakan pilih produk terlebih dahulu</p>
                    <a href="index.php" class="btn btn-primary" style="width: auto; margin-top: 1rem;">
                        <i class="fas fa-store"></i> Belanja Sekarang
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($keranjang as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php 
                                $icon = 'fa-box';
                                if (stripos($item['nama_produk'], 'sepatu') !== false) $icon = 'fa-shoe-prints';
                                elseif (stripos($item['nama_produk'], 'tas') !== false) $icon = 'fa-briefcase';
                                elseif (stripos($item['nama_produk'], 'jam') !== false) $icon = 'fa-clock';
                                elseif (stripos($item['nama_produk'], 'kamera') !== false) $icon = 'fa-camera';
                                elseif (stripos($item['nama_produk'], 'headphone') !== false) $icon = 'fa-headphones';
                                elseif (stripos($item['nama_produk'], 'laptop') !== false) $icon = 'fa-laptop';
                                elseif (stripos($item['nama_produk'], 'keyboard') !== false) $icon = 'fa-keyboard';
                                elseif (stripos($item['nama_produk'], 'mouse') !== false) $icon = 'fa-mouse';
                                ?>
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div class="item-details">
                                <h3 class="item-name"><?= htmlspecialchars($item['nama_produk']) ?></h3>
                                <div class="item-price"><?= formatRupiah($item['harga']) ?></div>
                                <div class="item-stok">Stok tersedia: <?= $item['stok'] ?></div>
                            </div>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="keranjang_id" value="<?= $item['keranjang_id'] ?>">
                                <div class="quantity-control">
                                    <button type="submit" name="update_jumlah" value="1" class="quantity-btn">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="jumlah" value="<?= $item['jumlah'] ?>" 
                                           min="0" max="<?= $item['stok'] ?>" class="quantity-input">
                                    <button type="submit" name="update_jumlah" value="1" class="quantity-btn">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="item-actions">
                                <a href="?hapus=<?= $item['keranjang_id'] ?>" class="btn-delete" 
                                   onclick="return confirm('Yakin hapus produk ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3 class="summary-title">
                        <i class="fas fa-receipt"></i> Ringkasan Belanja
                    </h3>
                    <div class="summary-row">
                        <span>Total Items</span>
                        <span><?= count($keranjang) ?> produk</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?= formatRupiah($total) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Checkout
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-store"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

